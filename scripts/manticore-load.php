#!/usr/bin/php
<?php
/**
 * Async bulk-insert loader for Manticore Search.
 *
 * CLI usage:
 *   php manticore-load.php <batch_size> <concurrency> <total_docs>
 *
 * Programmatic usage:
 *   require 'manticore-load.php';
 *   $result = manticore_load_run(['batch_size' => 1000, 'concurrency' => 8, 'total_docs' => 500000]);
 *
 * Environment variables (optional, override defaults):
 *   ML_HOST, ML_PORT, ML_TABLE, ML_SHARDS, ML_TABLE_OPTIONS
 *
 * Returns / prints:
 *   docs_per_sec  (int)
 */

/**
 * Run an async bulk-insert benchmark.
 *
 * @param array{
 *   batch_size?: int,
 *   concurrency?: int,
 *   total_docs?: int,
 *   host?: string,
 *   port?: int,
 *   table?: string,
 *   shards?: int,
 *   table_options?: string,
 *   quiet?: bool,
 * } $config
 * @return array{
 *   docs_per_sec: int,
 *   elapsed: float,
 *   batch_size: int,
 *   concurrency: int,
 *   total_docs: int,
 *   shards: int,
 *   latency_avg_ms: float|null,
 *   latency_p99_ms: float|null,
 * }
 */
function manticore_load_run(array $config): array
{
    $batch_size  = (int)($config['batch_size']  ?? 1000);
    $concurrency = (int)($config['concurrency'] ?? 1);
    $total_docs  = (int)($config['total_docs']  ?? 100000);
    $quiet       = (bool)($config['quiet']       ?? false);

    $host          = $config['host']          ?? getenv('ML_HOST')          ?: '127.0.0.1';
    $port          = (int)($config['port']    ?? getenv('ML_PORT')        ?: 9306);
    $table         = $config['table']         ?? getenv('ML_TABLE')         ?: 'user';
    $shards        = (int)($config['shards']  ?? getenv('ML_SHARDS')       ?: 0);
    $table_options = $config['table_options'] ?? getenv('ML_TABLE_OPTIONS') ?: "hitless_words='all' rt_mem_limit='16M'";

    if ($shards > 0) {
        $table_options .= " shards='$shards' rf='1'";
    }

    $all_links = [];
    $requests  = [];
    $latencies = [];

    for ($i = 0; $i < $concurrency; $i++) {
        $m = @mysqli_connect($host, '', '', '', $port);
        if (mysqli_connect_error()) {
            throw new RuntimeException("Cannot connect to Manticore at $host:$port");
        }
        $all_links[] = $m;
    }

    mysqli_query($all_links[0], "DROP TABLE IF EXISTS `$table`");
    mysqli_query($all_links[0], "CREATE TABLE `$table`(area text, age int, active bit(1)) $table_options");

    $process = static function (string $query) use (&$all_links, &$requests, &$latencies): bool {
        foreach ($all_links as $k => $link) {
            if (!empty($requests[$k])) {
                continue;
            }
            mysqli_query($link, $query, MYSQLI_ASYNC);
            $requests[$k] = microtime(true);
            return true;
        }

        do {
            $links = $errors = $reject = $all_links;
            $count = @mysqli_poll($links, $errors, $reject, 0, 1000);
            if ($count <= 0) {
                continue;
            }

            foreach ($links as $link) {
                $res = @mysqli_reap_async_query($link);
                $i   = array_search($link, $all_links, true);

                if ($link->error) {
                    if (!$quiet) {
                        fwrite(STDERR, "ERROR in '" . substr($query, 0, 100) . "...': {$link->error}\n");
                    }
                    if (!mysqli_ping($link)) {
                        unset($all_links[$i], $requests[$i]);
                    }
                    return false;
                }

                if ($res === false) {
                    continue;
                }
                if (is_object($res)) {
                    mysqli_free_result($res);
                }

                $latencies[] = microtime(true) - $requests[$i];
                $requests[$i] = microtime(true);
                mysqli_query($link, $query, MYSQLI_ASYNC);
                return true;
            }
        } while (true);
    };

    $t           = microtime(true);
    $c           = 0;
    $batch       = [];
    $query_start = "INSERT INTO `$table`(id, area, age, active) VALUES ";
    $error       = false;

    while (count($all_links) && $c < $total_docs) {
        $batch[] = "(0,'" . substr(md5((string)rand()), 0, 6) . "'," . rand(5, 15) . "," . rand(0, 1) . ")";
        $c++;

        if (count($batch) === $batch_size) {
            if (!$process($query_start . implode(',', $batch))) {
                $error = true;
                break;
            }
            $batch = [];
        }
    }

    if (!$error && count($batch) > 0) {
        $process($query_start . implode(',', $batch));
    }

    do {
        $links = $errors = $reject = array_values($all_links);
        @mysqli_poll($links, $errors, $reject, 0, 100);
        $done = count($links) + count($errors) + count($reject);
    } while (count($all_links) !== $done);

    foreach ($all_links as $link) {
        mysqli_close($link);
    }

    $elapsed     = microtime(true) - $t;
    $docs_per_sec = $elapsed > 0 ? (int)round($total_docs / $elapsed) : 0;

    $latency_avg_ms = null;
    $latency_p99_ms = null;
    if (count($latencies) > 0) {
        sort($latencies);
        $latency_avg_ms = (array_sum($latencies) / count($latencies)) * 1000;
        $latency_p99_ms = $latencies[(int)(count($latencies) * 0.99)] * 1000;
    }

    if (!$quiet) {
        echo "finished inserting\n";
        echo "$docs_per_sec docs per sec\n";
        if ($latency_avg_ms !== null) {
            fprintf(
                STDERR,
                "latency avg=%.1fms  p99=%.1fms  concurrency=%d  batch=%d\n",
                $latency_avg_ms,
                $latency_p99_ms,
                $concurrency,
                $batch_size
            );
        }
    }

    return [
        'docs_per_sec'     => $docs_per_sec,
        'elapsed'          => $elapsed,
        'batch_size'       => $batch_size,
        'concurrency'      => $concurrency,
        'total_docs'       => $total_docs,
        'shards'           => $shards,
        'latency_avg_ms'   => $latency_avg_ms,
        'latency_p99_ms'   => $latency_p99_ms,
    ];
}

// ── CLI entry point ───────────────────────────────────────────────────────────

if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    if (count($argv) < 4) {
        die("Usage: " . basename(__FILE__) . " <batch_size> <concurrency> <total_docs>\n");
    }

    try {
        manticore_load_run([
            'batch_size'  => (int)$argv[1],
            'concurrency' => (int)$argv[2],
            'total_docs'  => (int)$argv[3],
        ]);
    } catch (RuntimeException $e) {
        fwrite(STDERR, $e->getMessage() . "\n");
        exit(1);
    }
}
