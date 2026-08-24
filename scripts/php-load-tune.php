#!/usr/bin/php
<?php
/**
 * Auto-tune manticore-load.php parameters for maximum insert throughput.
 *
 * Sequential search (one axis at a time — concurrency and batch interact only
 * weakly for inserts, so a full Cartesian product is usually wasted work):
 *   1. Sweep concurrency with a fixed seed batch size
 *   2. Sweep batch size with the winning concurrency
 *   3. Optional shard sweep with both fixed
 *   4. Fine-tune each axis around the winner (half / center / double)
 *   5. Final validation run with the full doc count
 *
 * Usage:
 *   php php-load-tune.php [options]
 */

require __DIR__ . '/manticore-load.php';

final class PhpLoadTuner
{
    private string $host;
    private int $port;
    private string $table;
    private string $tableOptions;
    private int $probeDocs;
    private int $finalDocs;
    private bool $tuneShards;
    private bool $dryRun;
    private bool $verbose;

    /** @var int[]|null */
    private ?array $batchSizes = null;

    /** @var int[]|null */
    private ?array $concurrency = null;

    /** @var int[]|null */
    private ?array $shards = null;

    public function __construct(array $options = [])
    {
        $this->host         = $options['host']          ?? getenv('HOST')          ?: '127.0.0.1';
        $this->port         = (int)($options['port']    ?? getenv('PORT')         ?: 9306);
        $this->table        = $options['table']         ?? getenv('TABLE')         ?: 'user';
        $this->tableOptions = $options['table_options'] ?? getenv('TABLE_OPTIONS') ?: "hitless_words='all' rt_mem_limit='16M'";
        $this->probeDocs    = (int)($options['probe_docs'] ?? getenv('PROBE_DOCS') ?: 500000);
        $this->finalDocs    = (int)($options['final_docs'] ?? getenv('FINAL_DOCS') ?: 20000000);
        $this->tuneShards   = ($options['tune_shards'] ?? getenv('TUNE_SHARDS') ?: '1') !== '0';
        $this->dryRun       = (bool)($options['dry_run']  ?? false);
        $this->verbose      = (bool)($options['verbose'] ?? false);

        if (isset($options['batch_sizes'])) {
            $this->batchSizes = self::parseCsvInts($options['batch_sizes']);
        }
        if (isset($options['concurrency'])) {
            $this->concurrency = self::parseCsvInts($options['concurrency']);
        }
        if (isset($options['shards'])) {
            $this->shards = self::parseCsvInts($options['shards']);
        }
        if (array_key_exists('tune_shards', $options) && $options['tune_shards'] === false) {
            $this->tuneShards = false;
        }
    }

    public function run(): int
    {
        $cores = self::cpuCount();

        $batches = $this->batchSizes ?? self::pow2Range(64, 8192);
        $conc    = $this->concurrency ?? self::pow2Upto($cores * 2);
        // Seed batch for the concurrency sweep: prefer 1024, else mid-list.
        $seedBatch = in_array(1024, $batches, true) ? 1024 : $batches[(int)floor(count($batches) / 2)];

        $this->log("Target : {$this->host}:{$this->port}  table={$this->table}");
        $this->log("Cores  : $cores");
        $this->log("Probe  : {$this->probeDocs} docs/run");
        $this->log('');

        // Phase 1a — concurrency with fixed seed batch
        $this->log("Phase 1a — concurrency sweep (batch fixed at $seedBatch)");
        $this->log('  concurrency: ' . implode(',', $conc));
        $best = $this->sweepAxis('concurrency', $conc, $seedBatch, $conc[0], $this->probeDocs);
        $this->log("Concurrency winner: concurrency={$best['concurrency']} dps={$best['docs_per_sec']}");

        // Phase 1b — batch size with winning concurrency
        $this->log('');
        $this->log("Phase 1b — batch-size sweep (concurrency fixed at {$best['concurrency']})");
        $this->log('  batch: ' . implode(',', $batches));
        $best = $this->sweepAxis('batch', $batches, $batches[0], $best['concurrency'], $this->probeDocs);
        $this->log("Batch winner: batch={$best['batch_size']} concurrency={$best['concurrency']} dps={$best['docs_per_sec']}");

        // Phase 2 — shards
        $bestShards = 0;
        if ($this->tuneShards) {
            $shardList = $this->shards ?? [1, 2, 4, 8, 16];

            $this->log('');
            $this->log("Phase 2 — shard sweep (batch={$best['batch_size']} concurrency={$best['concurrency']})");

            $shardBest = ['docs_per_sec' => 0, 'shards' => 0];
            foreach ($shardList as $shard) {
                $result = $this->runOne($best['batch_size'], $best['concurrency'], $this->probeDocs, $shard);
                $this->logResult($best['batch_size'], $best['concurrency'], $shard, $result['docs_per_sec']);
                if ($result['docs_per_sec'] > $shardBest['docs_per_sec']) {
                    $shardBest = ['docs_per_sec' => $result['docs_per_sec'], 'shards' => $shard];
                }
            }

            if ($shardBest['docs_per_sec'] > $best['docs_per_sec']) {
                $best['docs_per_sec'] = $shardBest['docs_per_sec'];
                $bestShards           = $shardBest['shards'];
                $this->log("Shard sweep winner: shards=$bestShards dps={$best['docs_per_sec']}");
            } else {
                $bestShards = $shardBest['shards'];
                $this->log("Shard sweep: no sharded config beat unsharded (best shards=$bestShards at {$shardBest['docs_per_sec']} dps)");
            }
        }

        // Phase 3 — fine-tune each axis around the winner (still sequential, not Cartesian)
        $this->log('');
        $this->log('Phase 3 — fine tune');

        $fineConc = self::neighbors($best['concurrency']);
        $this->log("  concurrency neighbors: " . implode(',', $fineConc));
        $refined = $this->sweepAxis(
            'concurrency',
            $fineConc,
            $best['batch_size'],
            $best['concurrency'],
            $this->probeDocs,
            $bestShards ?: null
        );
        if ($refined['docs_per_sec'] > $best['docs_per_sec']) {
            $best = $refined;
            $this->log("Fine concurrency improved: concurrency={$best['concurrency']} dps={$best['docs_per_sec']}");
        }

        $fineBatches = self::neighbors($best['batch_size']);
        $this->log("  batch neighbors: " . implode(',', $fineBatches));
        $refined = $this->sweepAxis(
            'batch',
            $fineBatches,
            $best['batch_size'],
            $best['concurrency'],
            $this->probeDocs,
            $bestShards ?: null
        );
        if ($refined['docs_per_sec'] > $best['docs_per_sec']) {
            $best = $refined;
            $this->log("Fine batch improved: batch={$best['batch_size']} dps={$best['docs_per_sec']}");
        } else {
            $this->log("Fine tune: no further improvement");
        }

        $shardLabel = $bestShards > 0 ? (string)$bestShards : 'none (plain RT table)';

        $this->log('');
        $this->log('═══════════════════════════════════════════════');
        $this->log('Best probe result:');
        $this->log("  batch-size  = {$best['batch_size']}");
        $this->log("  concurrency = {$best['concurrency']}");
        $this->log("  shards      = $shardLabel");
        $this->log("  docs/sec    = {$best['docs_per_sec']}  (over {$this->probeDocs} docs)");
        $this->log('═══════════════════════════════════════════════');

        if ($this->dryRun) {
            $this->log('Dry run complete.');
            return 0;
        }

        $this->log('');
        $this->log("Final validation run: {$this->finalDocs} docs");

        $cmd = sprintf(
            'php %s %d %d %d',
            basename(__DIR__ . '/manticore-load.php'),
            $best['batch_size'],
            $best['concurrency'],
            $this->finalDocs
        );
        if ($bestShards > 0) {
            $cmd .= "  # ML_SHARDS=$bestShards ML_TABLE={$this->table}";
        }
        echo "\nRecommended command:\n  $cmd\n\n";

        $final = $this->runOne($best['batch_size'], $best['concurrency'], $this->finalDocs, $bestShards ?: null);
        $this->log("Final: {$final['docs_per_sec']} docs/sec over {$this->finalDocs} docs");

        return 0;
    }

    /**
     * Sweep one axis while holding the other fixed.
     *
     * @param 'batch'|'concurrency' $axis
     * @param int[] $values
     * @return array{batch_size: int, concurrency: int, docs_per_sec: int}
     */
    private function sweepAxis(
        string $axis,
        array $values,
        int $fixedBatch,
        int $fixedConc,
        int $total,
        ?int $shards = null
    ): array {
        $best = null;

        foreach ($values as $value) {
            $batch = $axis === 'batch' ? $value : $fixedBatch;
            $conc  = $axis === 'concurrency' ? $value : $fixedConc;

            $result = $this->runOne($batch, $conc, $total, $shards);
            $this->logResult($batch, $conc, $shards ?? 0, $result['docs_per_sec']);

            if ($best === null || $result['docs_per_sec'] > $best['docs_per_sec']) {
                $best = [
                    'batch_size'   => $batch,
                    'concurrency'  => $conc,
                    'docs_per_sec' => $result['docs_per_sec'],
                ];
            }
        }

        return $best ?? [
            'batch_size'   => $fixedBatch,
            'concurrency'  => $fixedConc,
            'docs_per_sec' => 0,
        ];
    }

    private function runOne(int $batch, int $conc, int $total, ?int $shards = null): array
    {
        if ($this->dryRun) {
            $shardLabel = $shards ? (string)$shards : 'none';
            fprintf(
                STDERR,
                "DRY_RUN  batch=%-6d conc=%-4d total=%-10d shards=%s\n",
                $batch,
                $conc,
                $total,
                $shardLabel
            );
            return ['docs_per_sec' => 0];
        }

        if ($this->verbose) {
            $shardLabel = $shards ? (string)$shards : 'none';
            $this->log("Running: batch=$batch conc=$conc total=$total shards=$shardLabel");
        }

        try {
            return manticore_load_run([
                'batch_size'    => $batch,
                'concurrency'   => $conc,
                'total_docs'    => $total,
                'host'          => $this->host,
                'port'          => $this->port,
                'table'         => $this->table,
                'shards'        => $shards ?? 0,
                'table_options' => $this->tableOptions,
                'quiet'         => true,
            ]);
        } catch (RuntimeException $e) {
            $this->log('ERROR: ' . $e->getMessage());
            return ['docs_per_sec' => 0];
        }
    }

    private function logResult(int $batch, int $conc, int $shards, int $dps): void
    {
        $shardLabel = $shards > 0 ? (string)$shards : 'none';
        fprintf(STDERR, "result\tbatch=%d\tconc=%d\tshards=%s\tdps=%d\n", $batch, $conc, $shardLabel, $dps);
    }

    private function log(string $message): void
    {
        fwrite(STDERR, "[tune] $message\n");
    }

    /** @return int[] */
    private static function parseCsvInts(string $csv): array
    {
        return array_map('intval', array_filter(array_map('trim', explode(',', $csv)), 'strlen'));
    }

    private static function cpuCount(): int
    {
        if (is_readable('/proc/cpuinfo')) {
            $count = substr_count(file_get_contents('/proc/cpuinfo'), 'processor');
            if ($count > 0) {
                return $count;
            }
        }
        $n = (int)(getenv('NUMBER_OF_PROCESSORS') ?: 0);
        return $n > 0 ? $n : 8;
    }

    /** @return int[] Powers of 2 from $min up to $max (inclusive). */
    private static function pow2Range(int $min, int $max): array
    {
        $values = [];
        $v = 1;
        while ($v < $min) {
            $v *= 2;
        }
        while ($v <= $max) {
            $values[] = $v;
            $v *= 2;
        }
        return $values;
    }

    /** @return int[] */
    private static function pow2Upto(int $max): array
    {
        $values = [];
        for ($v = 1; $v <= $max; $v *= 2) {
            $values[] = $v;
        }
        return $values;
    }

    /** @return int[] */
    private static function neighbors(int $center): array
    {
        $half = max(1, intdiv($center, 2));
        $seen = [];
        $out  = [];
        foreach ([$half, $center, $center * 2] as $v) {
            if (!isset($seen[$v])) {
                $seen[$v] = true;
                $out[]    = $v;
            }
        }
        return $out;
    }

    public static function usage(): void
    {
        $script = basename(__FILE__);
        echo <<<EOF
Usage: php $script [options]

Sequentially tunes concurrency, then batch size, then shards of
manticore-load.php to maximize docs/sec (not a full Cartesian product).

Options:
  --host=HOST            Manticore host (default: 127.0.0.1)
  --port=PORT            Manticore MySQL port (default: 9306)
  --table=NAME           Table name (default: user)
  --probe-docs=N         Docs per probe run (default: 500000)
  --final-docs=N         Docs for final validation (default: 20000000)
  --batch-sizes=LIST     Comma-separated batch sizes (default: 64,128,...,8192)
  --concurrency=LIST     Comma-separated concurrency values (default: powers of 2 up to 2×CPU)
  --no-shards            Skip shard-count sweep
  --shards=LIST          Shard counts to test (default: 1,2,4,8,16)
  --dry-run              Print grid without executing
  --verbose              Show each run before executing
  -h, --help             Show this help

Environment variables (override defaults):
  HOST, PORT, TABLE, PROBE_DOCS, FINAL_DOCS, TUNE_SHARDS, TABLE_OPTIONS

Examples:
  php scripts/php-load-tune.php
  php scripts/php-load-tune.php --probe-docs=200000 --final-docs=5000000
  php scripts/php-load-tune.php --batch-sizes=512,1024 --concurrency=4,8,16
  php scripts/php-load-tune.php --no-shards --verbose

EOF;
    }
}

// ── CLI entry point ───────────────────────────────────────────────────────────

if (PHP_SAPI !== 'cli' || realpath($argv[0] ?? '') !== __FILE__) {
    return;
}

$options = [
    'dry_run'    => false,
    'verbose'    => false,
    'tune_shards'=> true,
];

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '-h' || $arg === '--help') {
        PhpLoadTuner::usage();
        exit(0);
    }
    if ($arg === '--dry-run') {
        $options['dry_run'] = true;
        continue;
    }
    if ($arg === '--verbose') {
        $options['verbose'] = true;
        continue;
    }
    if ($arg === '--no-shards') {
        $options['tune_shards'] = false;
        continue;
    }
    if (strpos($arg, '=') === false) {
        fwrite(STDERR, "[tune] ERROR: Unknown option: $arg (try --help)\n");
        exit(1);
    }
    [$key, $value] = explode('=', $arg, 2);
    switch ($key) {
        case '--host':
            $options['host'] = $value;
            break;
        case '--port':
            $options['port'] = (int)$value;
            break;
        case '--table':
            $options['table'] = $value;
            break;
        case '--probe-docs':
            $options['probe_docs'] = (int)$value;
            break;
        case '--final-docs':
            $options['final_docs'] = (int)$value;
            break;
        case '--batch-sizes':
            $options['batch_sizes'] = $value;
            break;
        case '--concurrency':
            $options['concurrency'] = $value;
            break;
        case '--shards':
            $options['shards'] = $value;
            break;
        default:
            fwrite(STDERR, "[tune] ERROR: Unknown option: $key (try --help)\n");
            exit(1);
    }
}

exit((new PhpLoadTuner($options))->run());
