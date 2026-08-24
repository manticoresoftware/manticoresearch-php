#!/usr/bin/env bash
#
# Auto-tune manticore-load parameters for maximum insert throughput.
#
# Strategy (inspired by the sharding insert benchmarks):
#   1. Coarse grid over threads x batch-size (short probe runs)
#   2. Optional shard-count sweep around the best threads/batch
#   3. Fine grid around the best threads/batch
#   4. Final validation run with the full document count
#
# Requires: manticore-load, python3, a running Manticore instance.
#
set -euo pipefail

MANTICORE_LOAD="${MANTICORE_LOAD:-manticore-load}"
HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-9306}"
TABLE="${TABLE:-ml_tune}"
PROBE_TOTAL="${PROBE_TOTAL:-500000}"
FINAL_TOTAL="${FINAL_TOTAL:-20000000}"
TUNE_SHARDS="${TUNE_SHARDS:-1}"
DRY_RUN="${DRY_RUN:-0}"
VERBOSE="${VERBOSE:-0}"

# Default schema/load from the sharding blog benchmark.
INIT_SQL="CREATE TABLE ${TABLE}(id bigint, name text, type int)"
LOAD_SQL="INSERT INTO ${TABLE}(id,name,type) VALUES(<increment>,'<text/10/100>',<int/1/100>)"

usage() {
    cat <<'EOF'
Usage: manticore-load-tune.sh [options]

Automatically searches manticore-load --threads and --batch-size (and
optionally shard count) to maximize docs/sec on INSERT workloads.

Options:
  --host=HOST              Manticore host (default: 127.0.0.1)
  --port=PORT              Manticore SQL port (default: 9306)
  --table=NAME             Table name for tuning runs (default: ml_tune)
  --probe-total=N          Docs per probe run (default: 500000)
  --final-total=N          Docs for final validation (default: 20000000)
  --init=SQL               CREATE TABLE statement (table name substituted)
  --load=SQL               INSERT template (default: blog benchmark shape)
  --no-shards              Skip shard-count sweep
  --shards=LIST            Explicit shard counts to test (e.g. 1,2,4,8)
  --threads=LIST           Explicit thread counts for coarse phase
  --batch-sizes=LIST       Explicit batch sizes for coarse phase
  --dry-run                Print planned runs without executing
  --verbose                Show each manticore-load invocation
  -h, --help               Show this help

Environment:
  MANTICORE_LOAD           Path to manticore-load (default: manticore-load)

Examples:
  ./scripts/manticore-load-tune.sh
  ./scripts/manticore-load-tune.sh --probe-total=200000 --final-total=5000000
  ./scripts/manticore-load-tune.sh --init="CREATE TABLE t(id bigint, name text, type int) shards='4' rf='1'"
EOF
}

log() {
    printf '[tune] %s\n' "$*" >&2
}

die() {
    printf '[tune] ERROR: %s\n' "$*" >&2
    exit 1
}

require_cmd() {
    command -v "$1" >/dev/null 2>&1 || die "Required command not found: $1"
}

# Build a comma-separated list of powers-of-two up to max (inclusive).
pow2_upto() {
    local max="$1"
    local v=1
    local out=""
    while [[ "$v" -le "$max" ]]; do
        if [[ -n "$out" ]]; then
            out+=","
        fi
        out+="$v"
        v=$((v * 2))
    done
    printf '%s' "$out"
}

# Expand coarse batch-size candidates.
default_batch_sizes() {
    printf '%s' "64,128,256,512,1024,2048,4096,8192"
}

# Run one manticore-load grid and print JSON result objects (one per line).
run_grid() {
    local label="$1"
    local threads_csv="$2"
    local batch_csv="$3"
    local total="$4"
    local init_sql="$5"
    local column="${6:-}"

    local -a cmd=(
        "$MANTICORE_LOAD"
        --quiet --json
        --host="$HOST"
        --port="$PORT"
        --threads="$threads_csv"
        --batch-size="$batch_csv"
        --total="$total"
        --drop
        --init="$init_sql"
        --load="$LOAD_SQL"
    )

    if [[ -n "$column" ]]; then
        cmd+=(--column="$column")
    fi

    if [[ "$VERBOSE" == "1" ]]; then
        log "Running grid ($label): threads=[$threads_csv] batch=[$batch_csv] total=$total"
        printf '  %q ' "${cmd[@]}" >&2
        printf '\n' >&2
    fi

    if [[ "$DRY_RUN" == "1" ]]; then
        printf 'DRY_RUN\t%s\t%s\t%s\t%s\n' "$label" "$threads_csv" "$batch_csv" "$total"
        return 0
    fi

    # manticore-load prints one JSON object per configuration.
    "${cmd[@]}" 2>/dev/null | python3 - "$label" <<'PY'
import json
import sys

label = sys.argv[1]
text = sys.stdin.read().strip()
if not text:
    sys.exit(0)

# Accept either one JSON object or several back-to-back objects/lines.
chunks = []
buf = ""
depth = 0
for ch in text:
    buf += ch
    if ch == '{':
        depth += 1
    elif ch == '}':
        depth -= 1
        if depth == 0:
            chunks.append(buf.strip())
            buf = ""

for chunk in chunks:
    if not chunk:
        continue
    try:
        obj = json.loads(chunk)
    except json.JSONDecodeError:
        continue
    obj["phase"] = label
    if "custom_column" in obj:
        cc = obj["custom_column"]
        obj[cc.get("name", "custom")] = cc.get("value")
    print(json.dumps(obj))
PY
}

pick_best() {
    python3 - <<'PY'
import json
import sys

best = None
for line in sys.stdin:
    line = line.strip()
    if not line or line.startswith("DRY_RUN"):
        continue
    try:
        row = json.loads(line)
    except json.JSONDecodeError:
        continue
    ops = row.get("operations_per_second")
    if ops in (None, "N/A"):
        continue
    try:
        ops = int(ops)
    except (TypeError, ValueError):
        continue
    if best is None or ops > best["operations_per_second"]:
        best = row
        best["operations_per_second"] = ops

if best is None:
    sys.exit(1)

print(json.dumps(best))
PY
}

neighbors() {
    local center="$1"
    python3 - "$center" <<'PY'
import sys

center = int(sys.argv[1])
candidates = sorted({max(1, center // 2), center, center * 2})
print(",".join(str(v) for v in candidates))
PY
}

main() {
    local explicit_threads=""
    local explicit_batches=""
    local explicit_shards=""
    local skip_shards=0

    while [[ $# -gt 0 ]]; do
        case "$1" in
            --host=*) HOST="${1#*=}" ;;
            --port=*) PORT="${1#*=}" ;;
            --table=*) TABLE="${1#*=}"; INIT_SQL="CREATE TABLE ${TABLE}(id bigint, name text, type int)" ;;
            --probe-total=*) PROBE_TOTAL="${1#*=}" ;;
            --final-total=*) FINAL_TOTAL="${1#*=}" ;;
            --init=*) INIT_SQL="${1#*=}" ;;
            --load=*) LOAD_SQL="${1#*=}" ;;
            --no-shards) skip_shards=1 ;;
            --shards=*) explicit_shards="${1#*=}"; skip_shards=0 ;;
            --threads=*) explicit_threads="${1#*=}" ;;
            --batch-sizes=*) explicit_batches="${1#*=}" ;;
            --dry-run) DRY_RUN=1 ;;
            --verbose) VERBOSE=1 ;;
            -h|--help) usage; exit 0 ;;
            *) die "Unknown option: $1 (try --help)" ;;
        esac
        shift
    done

    require_cmd "$MANTICORE_LOAD"
    require_cmd python3

    local cores threads_csv batch_csv results best_json
    cores="$(nproc 2>/dev/null || getconf _NPROCESSORS_ONLN 2>/dev/null || echo 8)"

    if [[ -n "$explicit_threads" ]]; then
        threads_csv="$explicit_threads"
    else
        # Blog used 32 writers on a 16-core / 32-thread box; sweep up to 2x cores.
        threads_csv="$(pow2_upto $((cores * 2)))"
    fi

    if [[ -n "$explicit_batches" ]]; then
        batch_csv="$explicit_batches"
    else
        batch_csv="$(default_batch_sizes)"
    fi

    log "Host=$HOST:$PORT table=$TABLE cores=$cores"
    log "Phase 1/3: coarse grid threads=[$threads_csv] batch=[$batch_csv] probe_total=$PROBE_TOTAL"

    if [[ "$DRY_RUN" == "1" ]]; then
        run_grid "coarse" "$threads_csv" "$batch_csv" "$PROBE_TOTAL" "$INIT_SQL" >/dev/null
        log "Dry run complete (no Manticore connection required)."
        exit 0
    fi

    results="$(run_grid "coarse" "$threads_csv" "$batch_csv" "$PROBE_TOTAL" "$INIT_SQL")"
    best_json="$(printf '%s\n' "$results" | pick_best)" || die "No successful probe runs. Is Manticore reachable on $HOST:$PORT?"

    local best_threads best_batch best_ops
    best_threads="$(python3 -c 'import json,sys; print(json.load(sys.stdin)["threads"])' <<<"$best_json")"
    best_batch="$(python3 -c 'import json,sys; print(json.load(sys.stdin)["batch_size"])' <<<"$best_json")"
    best_ops="$(python3 -c 'import json,sys; print(json.load(sys.stdin)["operations_per_second"])' <<<"$best_json")"
    log "Coarse best: threads=$best_threads batch=$best_batch docs/sec=$best_ops"

    local best_shards=""
    if [[ "$skip_shards" -eq 0 && "$TUNE_SHARDS" == "1" ]]; then
        local shard_list shard
        if [[ -n "$explicit_shards" ]]; then
            shard_list="${explicit_shards//,/ }"
        else
            # Blog peak was around 4-8 shards on 16 cores; avoid very large counts.
            shard_list="1 2 4 8 16"
        fi

        log "Phase 2/3: shard sweep (fixed threads=$best_threads batch=$best_batch)"
        local shard_results=""
        for shard in $shard_list; do
            local init_sharded="${INIT_SQL}"
            if [[ "$init_sharded" != *"shards="* ]]; then
                init_sharded="${init_sharded%)} shards='${shard}' rf='1')"
            else
                init_sharded="$(python3 - "$init_sharded" "$shard" <<'PY'
import re, sys
sql, shard = sys.argv[1], sys.argv[2]
print(re.sub(r"shards\s*=\s*'[^']*'", f"shards='{shard}'", sql))
PY
)"
            fi
            shard_results+="$(run_grid "shards" "$best_threads" "$best_batch" "$PROBE_TOTAL" "$init_sharded" "shards/$shard")"$'\n'
        done

        local shard_best
        shard_best="$(printf '%s\n' "$shard_results" | pick_best)" || shard_best="$best_json"
        best_json="$shard_best"
        best_threads="$(python3 -c 'import json,sys; print(json.load(sys.stdin)["threads"])' <<<"$best_json")"
        best_batch="$(python3 -c 'import json,sys; print(json.load(sys.stdin)["batch_size"])' <<<"$best_json")"
        best_ops="$(python3 -c 'import json,sys; print(json.load(sys.stdin)["operations_per_second"])' <<<"$best_json")"
        if python3 -c 'import json,sys; print(json.load(sys.stdin).get("shards",""))' <<<"$best_json" | grep -qv '^$'; then
            best_shards="$(python3 -c 'import json,sys; print(json.load(sys.stdin).get("shards",""))' <<<"$best_json")"
        fi
        log "Shard sweep best: threads=$best_threads batch=$best_batch shards=${best_shards:-n/a} docs/sec=$best_ops"
    fi

    local fine_threads fine_batches fine_results fine_best
    fine_threads="$(neighbors "$best_threads")"
    fine_batches="$(neighbors "$best_batch")"
    log "Phase 3/3: fine grid threads=[$fine_threads] batch=[$fine_batches]"

    local init_final="$INIT_SQL"
    if [[ -n "$best_shards" ]]; then
        init_final="${INIT_SQL%)} shards='${best_shards}' rf='1')"
        if [[ "$INIT_SQL" == *"shards="* ]]; then
            init_final="$(python3 - "$INIT_SQL" "$best_shards" <<'PY'
import re, sys
sql, shard = sys.argv[1], sys.argv[2]
print(re.sub(r"shards\s*=\s*'[^']*'", f"shards='{shard}'", sql))
PY
)"
        fi
    fi

    fine_results="$(run_grid "fine" "$fine_threads" "$fine_batches" "$PROBE_TOTAL" "$init_final")"
    fine_best="$(printf '%s\n' "$fine_results" | pick_best)" || fine_best="$best_json"
    best_json="$fine_best"

    best_threads="$(python3 -c 'import json,sys; print(json.load(sys.stdin)["threads"])' <<<"$best_json")"
    best_batch="$(python3 -c 'import json,sys; print(json.load(sys.stdin)["batch_size"])' <<<"$best_json")"
    best_ops="$(python3 -c 'import json,sys; print(json.load(sys.stdin)["operations_per_second"])' <<<"$best_json")"
    if [[ -z "$best_shards" ]]; then
        best_shards="$(python3 -c 'import json,sys; print(json.load(sys.stdin).get("shards",""))' <<<"$best_json" 2>/dev/null || true)"
    fi

    log "Fine grid best (probe): threads=$best_threads batch=$best_batch shards=${best_shards:-n/a} docs/sec=$best_ops"

    log "Final validation: total=$FINAL_TOTAL"
    local -a final_cmd=(
        "$MANTICORE_LOAD"
        --host="$HOST"
        --port="$PORT"
        --threads="$best_threads"
        --batch-size="$best_batch"
        --total="$FINAL_TOTAL"
        --drop
        --init="$init_final"
        --load="$LOAD_SQL"
    )

    printf '\nRecommended command:\n'
    printf '  %q' "${final_cmd[@]}"
    printf '\n\n'

    "${final_cmd[@]}"
}

main "$@"
