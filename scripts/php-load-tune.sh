#!/usr/bin/env bash
#
# Auto-tune the async PHP loader (scripts/manticore-load.php) for maximum
# insert throughput by grid-searching batch-size and concurrency.
#
# Three phases:
#   1. Coarse grid  — batch × concurrency sweep with a small probe doc count
#   2. Shard sweep  — fix best batch/concurrency, vary shard count (optional)
#   3. Fine grid    — tighten around the coarse winner
#   4. Final run    — full doc count with the winning parameters
#
# The PHP script is expected to print a line matching:
#   <N> docs per sec
# on stdout or stderr when it finishes.
#
# Usage: see --help
#
set -euo pipefail

PHP="${PHP:-php}"
LOADER="${LOADER:-$(dirname "$0")/manticore-load.php}"
HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-9306}"       # MySQL protocol port (Manticore default is 9306)
TABLE="${TABLE:-user}"
PROBE_DOCS="${PROBE_DOCS:-500000}"
FINAL_DOCS="${FINAL_DOCS:-20000000}"
TUNE_SHARDS="${TUNE_SHARDS:-1}"
DRY_RUN="${DRY_RUN:-0}"
VERBOSE="${VERBOSE:-0}"

# Extra table options appended to the CREATE TABLE inside the PHP script.
# The PHP script currently hard-codes the schema; we pass it via env.
# If you have patched the PHP script to accept --table-options, set this.
TABLE_OPTIONS="${TABLE_OPTIONS:-hitless_words='all' rt_mem_limit='16M'}"

usage() {
    cat <<'EOF'
Usage: php-load-tune.sh [options]

Grid-searches batch-size and concurrency of scripts/manticore-load.php to
find the combination that delivers the most docs/sec.

Options:
  --loader=PATH          Path to the PHP loader script
                         (default: scripts/manticore-load.php)
  --php=PATH             PHP binary (default: php)
  --host=HOST            Manticore host (default: 127.0.0.1)
  --port=PORT            Manticore MySQL port (default: 9306)
  --table=NAME           Table name (default: user)
  --probe-docs=N         Docs per probe run (default: 500000)
  --final-docs=N         Docs for final validation (default: 20000000)
  --batch-sizes=LIST     Comma-separated batch sizes to try
                         (default: 64,128,256,512,1024,2048,4096,8192)
  --concurrency=LIST     Comma-separated concurrency values to try
                         (default: powers of 2 up to 2×CPU cores)
  --no-shards            Skip shard-count sweep
  --shards=LIST          Shard counts to test (default: 1,2,4,8,16)
  --dry-run              Print grid without executing
  --verbose              Show each PHP invocation before running it
  -h, --help             Show this help

Environment variables (override defaults):
  PHP                    PHP binary path
  LOADER                 Path to the PHP loader script
  HOST, PORT, TABLE      Connection / table settings
  PROBE_DOCS             Docs per probe run
  FINAL_DOCS             Docs for final validation
  TUNE_SHARDS            1 = run shard sweep, 0 = skip (default: 1)

The PHP script must accept:  <batch_size> <concurrency> <total_docs>
It must print a line like:   163000 docs per sec
(The loader script from the manticoresearch-php repo already does this.)

Examples:
  ./scripts/php-load-tune.sh
  ./scripts/php-load-tune.sh --probe-docs=200000 --final-docs=5000000
  ./scripts/php-load-tune.sh --batch-sizes=500,1000 --concurrency=4,8,16
  ./scripts/php-load-tune.sh --no-shards --verbose
EOF
}

log()  { printf '[tune] %s\n' "$*" >&2; }
die()  { printf '[tune] ERROR: %s\n' "$*" >&2; exit 1; }

require_cmd() {
    command -v "$1" >/dev/null 2>&1 || die "Required command not found: $1"
}

pow2_upto() {
    local max="$1" v=1 out=""
    while [[ "$v" -le "$max" ]]; do
        out="${out:+$out,}$v"
        v=$((v * 2))
    done
    printf '%s' "$out"
}

# Expand "a,b,c" into individual values separated by spaces.
csv_to_words() { printf '%s' "${1//,/ }"; }

# Given a center value, return "half,center,double" (all ≥ 1), deduplicated.
neighbors() {
    local c="$1"
    local half=$(( c / 2 ))
    local dbl=$(( c * 2 ))
    [[ "$half" -lt 1 ]] && half=1
    local -A seen=()
    local out=""
    for v in "$half" "$c" "$dbl"; do
        if [[ -z "${seen[$v]+x}" ]]; then
            seen[$v]=1
            out="${out:+$out,}$v"
        fi
    done
    printf '%s' "$out"
}

# Run the PHP loader once and return the docs/sec figure, or 0 on failure.
# Args: batch  concurrency  total  [shard_count_or_empty]
run_one() {
    local batch="$1"
    local conc="$2"
    local total="$3"
    local shards="${4:-}"

    if [[ "$DRY_RUN" == "1" ]]; then
        printf 'DRY_RUN  batch=%-6s conc=%-4s total=%-10s shards=%s\n' \
               "$batch" "$conc" "$total" "${shards:-none}" >&2
        printf '0'
        return 0
    fi

    if [[ "$VERBOSE" == "1" ]]; then
        log "Running: $PHP $LOADER $batch $conc $total  (shards=${shards:-none})"
    fi

    # The PHP script connects to 127.0.0.1:PORT and hard-codes the table name.
    # We expose HOST/PORT/TABLE/SHARDS/TABLE_OPTIONS via environment so a
    # lightly patched version of the script can pick them up; unpatched scripts
    # just ignore them and use their own hard-coded values.
    local output
    output="$(
        ML_HOST="$HOST" ML_PORT="$PORT" ML_TABLE="$TABLE" \
        ML_SHARDS="${shards:-0}" ML_TABLE_OPTIONS="$TABLE_OPTIONS" \
        "$PHP" "$LOADER" "$batch" "$conc" "$total" 2>&1
    )" || true

    # Parse "163000 docs per sec" (the last such line wins).
    local dps
    dps="$(printf '%s\n' "$output" | grep -Eo '^[0-9]+ docs per sec$' | tail -1 | grep -Eo '^[0-9]+')" || true
    printf '%s' "${dps:-0}"
}

# Sweep a batch×concurrency grid and print tab-separated results.
# Args: batch_csv  conc_csv  total  [shards]
sweep_grid() {
    local batches_csv="$1"
    local conc_csv="$2"
    local total="$3"
    local shards="${4:-}"

    local best_dps=0 best_batch=0 best_conc=0

    for batch in $(csv_to_words "$batches_csv"); do
        for conc in $(csv_to_words "$conc_csv"); do
            local dps
            dps="$(run_one "$batch" "$conc" "$total" "$shards")"
            printf 'result\tbatch=%s\tconc=%s\tshards=%s\tdps=%s\n' \
                   "$batch" "$conc" "${shards:-none}" "$dps" >&2
            if [[ "$dps" -gt "$best_dps" ]]; then
                best_dps="$dps"
                best_batch="$batch"
                best_conc="$conc"
            fi
        done
    done

    # Print winner on stdout for capture by the caller.
    printf '%s %s %s' "$best_batch" "$best_conc" "$best_dps"
}

main() {
    local explicit_batches="" explicit_conc="" explicit_shards="" skip_shards=0

    while [[ $# -gt 0 ]]; do
        case "$1" in
            --loader=*)       LOADER="${1#*=}" ;;
            --php=*)          PHP="${1#*=}" ;;
            --host=*)         HOST="${1#*=}" ;;
            --port=*)         PORT="${1#*=}" ;;
            --table=*)        TABLE="${1#*=}" ;;
            --probe-docs=*)   PROBE_DOCS="${1#*=}" ;;
            --final-docs=*)   FINAL_DOCS="${1#*=}" ;;
            --batch-sizes=*)  explicit_batches="${1#*=}" ;;
            --concurrency=*)  explicit_conc="${1#*=}" ;;
            --no-shards)      skip_shards=1 ;;
            --shards=*)       explicit_shards="${1#*=}"; skip_shards=0 ;;
            --dry-run)        DRY_RUN=1 ;;
            --verbose)        VERBOSE=1 ;;
            -h|--help)        usage; exit 0 ;;
            *) die "Unknown option: $1 (try --help)" ;;
        esac
        shift
    done

    [[ "$DRY_RUN" == "1" ]] || require_cmd "$PHP"
    [[ -f "$LOADER" ]] || die "PHP loader not found: $LOADER"

    local cores
    cores="$(nproc 2>/dev/null || getconf _NPROCESSORS_ONLN 2>/dev/null || echo 8)"

    local batches_csv conc_csv
    batches_csv="${explicit_batches:-64,128,256,512,1024,2048,4096,8192}"
    conc_csv="${explicit_conc:-$(pow2_upto $((cores * 2)))}"

    log "Loader : $LOADER"
    log "Target : $HOST:$PORT  table=$TABLE"
    log "Cores  : $cores"
    log ""
    log "Phase 1/3 — coarse grid"
    log "  batch      : $batches_csv"
    log "  concurrency: $conc_csv"
    log "  probe docs : $PROBE_DOCS"

    read -r best_batch best_conc best_dps < <(
        sweep_grid "$batches_csv" "$conc_csv" "$PROBE_DOCS"
    ) || { best_batch=1000; best_conc=1; best_dps=0; }
    log "Coarse winner: batch=$best_batch concurrency=$best_conc dps=$best_dps"

    # ── Shard sweep ────────────────────────────────────────────────────────
    local best_shards=""
    if [[ "$skip_shards" -eq 0 && "$TUNE_SHARDS" == "1" && "$DRY_RUN" != "1" ]] \
       || [[ "$DRY_RUN" == "1" && "$skip_shards" -eq 0 ]]; then

        local shard_list
        if [[ -n "$explicit_shards" ]]; then
            shard_list="${explicit_shards//,/ }"
        else
            shard_list="1 2 4 8 16"
        fi

        log ""
        log "Phase 2/3 — shard sweep (batch=$best_batch concurrency=$best_conc)"

        local shard_best_dps=0 shard_best_n=""
        for shard in $shard_list; do
            local dps
            dps="$(run_one "$best_batch" "$best_conc" "$PROBE_DOCS" "$shard")"
            printf 'result\tbatch=%s\tconc=%s\tshards=%s\tdps=%s\n' \
                   "$best_batch" "$best_conc" "$shard" "$dps" >&2
            if [[ "$dps" -gt "$shard_best_dps" ]]; then
                shard_best_dps="$dps"
                shard_best_n="$shard"
            fi
        done

        if [[ "$shard_best_dps" -gt "$best_dps" ]]; then
            best_dps="$shard_best_dps"
            best_shards="$shard_best_n"
            log "Shard sweep winner: shards=$best_shards dps=$best_dps"
        else
            log "Shard sweep: no sharded config beat unsharded (best shards=$shard_best_n at $shard_best_dps dps)"
            # still record the best shard count for informational output
            best_shards="$shard_best_n"
        fi
    fi

    # ── Fine grid ──────────────────────────────────────────────────────────
    local fine_batches fine_conc
    fine_batches="$(neighbors "$best_batch")"
    fine_conc="$(neighbors "$best_conc")"

    log ""
    log "Phase 3/3 — fine grid"
    log "  batch      : $fine_batches"
    log "  concurrency: $fine_conc"

    read -r fb fc fdps < <(
        sweep_grid "$fine_batches" "$fine_conc" "$PROBE_DOCS" "${best_shards:-}"
    ) || { fb="$best_batch"; fc="$best_conc"; fdps=0; }

    if [[ "$fdps" -gt "$best_dps" ]]; then
        best_batch="$fb"
        best_conc="$fc"
        best_dps="$fdps"
        log "Fine grid improved: batch=$best_batch concurrency=$best_conc dps=$best_dps"
    else
        log "Fine grid: no improvement (keeping batch=$best_batch concurrency=$best_conc)"
    fi

    # ── Summary ────────────────────────────────────────────────────────────
    log ""
    log "═══════════════════════════════════════════════"
    log "Best probe result:"
    log "  batch-size  = $best_batch"
    log "  concurrency = $best_conc"
    log "  shards      = ${best_shards:-none (plain RT table)}"
    log "  docs/sec    = $best_dps  (over $PROBE_DOCS docs)"
    log "═══════════════════════════════════════════════"

    if [[ "$DRY_RUN" == "1" ]]; then
        log "Dry run complete."
        exit 0
    fi

    # ── Final validation run ───────────────────────────────────────────────
    log ""
    log "Final validation run: $FINAL_DOCS docs"

    local cmd_display="$PHP $LOADER $best_batch $best_conc $FINAL_DOCS"
    if [[ -n "$best_shards" ]]; then
        cmd_display+="  # ML_SHARDS=$best_shards ML_TABLE=$TABLE"
    fi
    printf '\nRecommended command:\n  %s\n\n' "$cmd_display"

    local final_dps
    final_dps="$(run_one "$best_batch" "$best_conc" "$FINAL_DOCS" "${best_shards:-}")"
    log "Final: $final_dps docs/sec over $FINAL_DOCS docs"
}

main "$@"
