#!/usr/bin/php
<?php
/**
 * Compare Table::addDocuments vs Table::addDocumentsStreaming insert throughput.
 *
 * Usage:
 *   php scripts/compare-add-documents.php [options]
 *
 * Defaults: 1M docs, addDocuments batched at 10k, streaming auto-tunes workers
 * (probe time included in the timed run). Document fields match the manticore-load
 * --http taxi_indexer template (<text/2/8>, timestamp/float ranges).
 */

declare(strict_types=1);

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_readable($autoload)) {
	fwrite(STDERR, "Missing vendor/autoload.php; run composer install first.\n");
	exit(1);
}
require $autoload;

use Manticoresearch\Bulk\IndexerBulk;
use Manticoresearch\Bulk\OperationNormalizer;
use Manticoresearch\Client;
use Manticoresearch\Connection;
use Manticoresearch\Endpoints\Bulk;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Response;
use Manticoresearch\Table;

/**
 * @return array{
 *   host:string,
 *   port:int,
 *   table:string,
 *   docs:int,
 *   batch_size:int,
 *   workers:?int,
 *   max_workers:?int,
 *   probe_docs:?int,
 *   help:bool
 * }
 */
function parse_options(array $argv): array {
	$opts = [
		'host' => getenv('MS_HOST') ?: '127.0.0.1',
		'port' => (int)(getenv('MS_PORT') ?: 9308),
		'table' => getenv('MS_TABLE') ?: 'taxi_indexer_bench',
		'docs' => 1000000,
		'batch_size' => 10000,
		'workers' => null,
		'max_workers' => null,
		'probe_docs' => null,
		'help' => false,
	];

	foreach (array_slice($argv, 1) as $arg) {
		if ($arg === '-h' || $arg === '--help') {
			$opts['help'] = true;
			continue;
		}
		if (strncmp($arg, '--', 2) !== 0) {
			throw new InvalidArgumentException("Unknown argument: $arg");
		}
		$eq = strpos($arg, '=');
		if ($eq === false) {
			throw new InvalidArgumentException("Option requires a value: $arg");
		}
		$key = substr($arg, 2, $eq - 2);
		$value = substr($arg, $eq + 1);
		switch ($key) {
			case 'host':
				$opts['host'] = $value;
				break;
			case 'port':
				$opts['port'] = (int)$value;
				break;
			case 'table':
				$opts['table'] = $value;
				break;
			case 'docs':
				$opts['docs'] = (int)$value;
				break;
			case 'batch-size':
				$opts['batch_size'] = (int)$value;
				break;
			case 'workers':
				$opts['workers'] = (int)$value;
				break;
			case 'max-workers':
				$opts['max_workers'] = (int)$value;
				break;
			case 'probe-docs':
				$opts['probe_docs'] = (int)$value;
				break;
			default:
				throw new InvalidArgumentException("Unknown option: --$key");
		}
	}

	foreach (['docs', 'batch_size', 'port'] as $requiredPositive) {
		if ($opts[$requiredPositive] < 1) {
			throw new InvalidArgumentException("$requiredPositive must be >= 1");
		}
	}
	foreach (['workers', 'max_workers', 'probe_docs'] as $optionalPositive) {
		if ($opts[$optionalPositive] !== null && $opts[$optionalPositive] < 1) {
			throw new InvalidArgumentException("$optionalPositive must be >= 1");
		}
	}

	return $opts;
}

function print_usage(): void {
	$script = basename(__FILE__);
	echo <<<EOF
Usage: php $script [options]

Compares addDocuments (chunked) vs addDocumentsStreaming (auto-tune by default)
on a taxi-like real-time table. Fresh table per method; no warmups.

Options:
  --host=HOST            HTTP host (default: 127.0.0.1 or MS_HOST)
  --port=PORT            HTTP port (default: 9308 or MS_PORT)
  --table=NAME           Table name (default: taxi_indexer_bench or MS_TABLE)
  --docs=N               Documents per method (default: 1000000)
  --batch-size=N         Chunk size for addDocuments (default: 10000)
  --workers=N            Fixed streaming workers (omit to auto-tune)
  --max-workers=N        Cap for streaming auto-tune / workers
  --probe-docs=N         Docs per worker during auto-tune probes
  -h, --help             Show this help

Notes:
  Both methods report the same phases: doc generation, wrap, NDJSON encode,
  HTTP transfer (curl_exec / curl_multi wait), and response JSON decode.

Examples:
  php scripts/compare-add-documents.php
  php scripts/compare-add-documents.php --docs=100000 --batch-size=5000
  php scripts/compare-add-documents.php --workers=4 --docs=500000

EOF;
}

/**
 * Synthetic taxi documents matching manticore-load --http template:
 *   id=<increment>
 *   pickup_datetime=<int/1735689600/1767225599>
 *   dropoff_datetime=<int/1767225600/1798761599>
 *   passenger_count=<int/1/6>
 *   trip_distance=<float/1/100>
 *   fare_amount=<float/3/1000>
 *   pickup=<text/2/8>, dropoff=<text/2/8>
 *
 * Ranges and <text/2/8> generation mirror manticore-load's HTTP query generator
 * (same word list / punctuation rules; floats with 8 decimal places).
 *
 * @return Generator<int, array<string, mixed>>
 */
function taxi_documents(int $count, int $startId = 1): Generator {
	// Match ManticoreHttpQueryGenerator constructor seeding for comparable distributions.
	srand(42);
	mt_srand(42);

	for ($i = 0; $i < $count; $i++) {
		yield [
			'id' => $startId + $i,
			'pickup_datetime' => rand(1735689600, 1767225599),
			'dropoff_datetime' => rand(1767225600, 1798761599),
			'passenger_count' => rand(1, 6),
			'trip_distance' => load_random_float(1.0, 100.0),
			'fare_amount' => load_random_float(3.0, 1000.0),
			'pickup' => load_random_text(2, 8),
			'dropoff' => load_random_text(2, 8),
		];
	}
}

/**
 * Same float formula as manticore-load HTTP <float/min/max> (8 decimals).
 */
function load_random_float(float $min, float $max, int $decimals = 8): float {
	return round($min + mt_rand() / mt_getrandmax() * ($max - $min), $decimals);
}

/**
 * Same <text/min/max> generation as manticore-load QueryGenerator::generateRandomText().
 */
function load_random_text(int $minWords, int $maxWords): string {
	static $punctuation = ['.', '!', '?', ',', ';'];
	static $words = null;
	static $wordsCount = null;

	if ($words === null) {
		$words = [
			'the', 'be', 'to', 'of', 'and', 'a', 'in', 'that', 'have', 'I',
			'it', 'for', 'not', 'on', 'with', 'he', 'as', 'you', 'do', 'at',
			'this', 'but', 'his', 'by', 'from', 'they', 'we', 'say', 'her', 'she',
			'would', 'could', 'should', 'will', 'may', 'might', 'must', 'shall', 'can', 'had',
			'has', 'was', 'were', 'been', 'being', 'am', 'is', 'are', 'does', 'did',
			'go', 'went', 'gone', 'see', 'saw', 'seen', 'take', 'took', 'taken', 'make',
			'made', 'find', 'found', 'get', 'got', 'give', 'gave', 'think', 'thought', 'know',
			'knew', 'come', 'came', 'tell', 'told', 'work', 'worked', 'call', 'called', 'try',
			'tried', 'ask', 'asked', 'need', 'needed', 'feel', 'felt', 'become', 'became', 'leave',
			'left', 'put', 'run', 'ran', 'bring', 'brought', 'begin', 'began', 'keep', 'kept',
			'hold', 'held', 'write', 'wrote', 'stand', 'stood', 'hear', 'heard', 'let', 'set',
			'meet', 'met', 'pay', 'paid', 'sit', 'sat', 'speak', 'spoke', 'lie', 'lay',
			'lead', 'led', 'read', 'grow', 'grew', 'lose', 'lost', 'fall', 'fell', 'send',
			'sent', 'build', 'built', 'understand', 'understood', 'draw', 'drew', 'break', 'broke', 'spend',
			'spent', 'cut', 'hurt', 'sell', 'sold', 'rise', 'rose', 'drive', 'drove', 'buy',
			'beautiful', 'happy', 'sad', 'angry', 'excited', 'tired', 'hungry', 'thirsty', 'cold', 'hot',
			'big', 'small', 'tall', 'short', 'fat', 'thin', 'old', 'young', 'rich', 'poor',
			'fast', 'slow', 'early', 'late', 'hard', 'soft', 'loud', 'quiet', 'clean', 'dirty',
			'dark', 'light', 'heavy', 'light', 'strong', 'weak', 'wet', 'dry', 'good', 'bad',
			'high', 'low', 'long', 'short', 'wide', 'narrow', 'deep', 'shallow', 'thick', 'thin',
			'smooth', 'rough', 'sharp', 'dull', 'sweet', 'sour', 'bitter', 'salty', 'fresh', 'stale',
			'new', 'old', 'modern', 'ancient', 'wild', 'tame', 'brave', 'afraid', 'proud', 'humble',
			'wise', 'foolish', 'clever', 'stupid', 'kind', 'cruel', 'gentle', 'rough', 'calm', 'angry',
			'busy', 'lazy', 'careful', 'careless', 'serious', 'funny', 'happy', 'sad', 'rich', 'poor',
			'healthy', 'sick', 'alive', 'dead', 'right', 'wrong', 'true', 'false', 'real', 'fake',
			'open', 'closed', 'empty', 'full', 'heavy', 'light', 'hard', 'soft', 'hot', 'cold',
			'summer', 'winter', 'spring', 'autumn', 'morning', 'evening', 'night', 'day', 'dawn', 'dusk',
			'north', 'south', 'east', 'west', 'up', 'down', 'left', 'right', 'front', 'back',
			'inside', 'outside', 'above', 'below', 'near', 'far', 'here', 'there', 'everywhere', 'nowhere',
			'always', 'never', 'sometimes', 'often', 'rarely', 'usually', 'now', 'then', 'soon', 'later',
			'today', 'tomorrow', 'yesterday', 'weekly', 'monthly', 'yearly', 'daily', 'nightly', 'hourly', 'instantly',
			'quickly', 'slowly', 'suddenly', 'gradually', 'carefully', 'carelessly', 'quietly', 'loudly', 'softly', 'harshly',
			'easily', 'hardly', 'simply', 'complexly', 'naturally', 'artificially', 'personally', 'professionally', 'publicly', 'privately',
			'legally', 'illegally', 'formally', 'informally', 'physically', 'mentally', 'emotionally', 'spiritually', 'socially', 'individually',
			'politically', 'economically', 'culturally', 'historically', 'scientifically', 'artistically', 'musically', 'technically', 'medically', 'educationally',
			'locally', 'globally', 'nationally', 'internationally', 'regionally', 'universally', 'specifically', 'generally', 'particularly', 'commonly',
			'normally', 'unusually', 'regularly', 'irregularly', 'frequently', 'infrequently', 'occasionally', 'constantly', 'permanently', 'temporarily',
			'actively', 'passively', 'positively', 'negatively', 'directly', 'indirectly', 'correctly', 'incorrectly', 'successfully', 'unsuccessfully',
			'fortunately', 'unfortunately', 'happily', 'unhappily', 'luckily', 'unluckily', 'surprisingly', 'expectedly', 'obviously', 'subtly',
			'definitely', 'possibly', 'probably', 'certainly', 'maybe', 'perhaps', 'surely', 'doubtfully', 'clearly', 'vaguely',
			'1', '2', '3', '4', '5', '10', '20', '50', '100', '1000',
		];
		$wordsCount = count($words);
	}

	$numWords = rand($minWords, $maxWords);
	$text = [];
	for ($i = 0; $i < $numWords; $i++) {
		$word = $words[rand(0, $wordsCount - 1)];
		if ($i === 0 || (isset($text[count($text) - 1]) && substr($text[count($text) - 1], -1) === '.')) {
			$word = ucfirst($word);
		}
		if ($i !== $numWords - 1 && rand(1, 100) <= 20) {
			$word .= $punctuation[array_rand($punctuation)];
		}
		$text[] = $word;
	}
	if ($text !== []) {
		$last = &$text[count($text) - 1];
		if (!in_array(substr($last, -1), $punctuation, true)) {
			$last .= '.';
		}
	}
	return implode(' ', $text);
}

function taxi_schema(): array {
	return [
		'pickup_datetime' => ['type' => 'timestamp'],
		'dropoff_datetime' => ['type' => 'timestamp'],
		'passenger_count' => ['type' => 'integer'],
		'trip_distance' => ['type' => 'float'],
		'fare_amount' => ['type' => 'float'],
		'pickup' => ['type' => 'text'],
		'dropoff' => ['type' => 'text'],
	];
}

function recreate_table(Client $client, string $tableName): Table {
	$table = $client->table($tableName);
	$table->drop(true);
	$table->create(taxi_schema());
	return $table;
}

/**
 * Mirror Table::addDocuments envelope building (same shape sent to /bulk).
 *
 * @param array<int, array<string, mixed>> $documents
 * @return array<int, array{insert: array{table:string,id:int,doc:array}}>
 */
function wrap_documents_for_bulk(string $tableName, array $documents): array {
	$toinsert = [];
	foreach ($documents as $document) {
		if (!isset($document['id'])) {
			throw new RuntimeException('Document id is required for timed bulk path');
		}
		$id = $document['id'];
		if (is_string($id) && !is_numeric($id)) {
			throw new RuntimeException('Incorrect document id passed');
		}
		$id = (int)$id;
		unset($document['id']);
		foreach ($document as $key => $value) {
			if ($value === null) {
				throw new RuntimeException("Error: The key '{$key}' in document has a null value.\n");
			}
		}
		$toinsert[] = [
			'insert' => [
				'table' => $tableName,
				'id' => $id,
				'doc' => $document,
			],
		];
	}
	return $toinsert;
}

/**
 * Encode + HTTP POST /bulk + response decode, with per-phase timers.
 * Mirrors Endpoints\Bulk::setBody + Transport\Http::execute (without logger work).
 *
 * @param array<int, array{insert: array}> $wrapped
 * @param array<string, float> $phases
 * @return array
 */
function bulk_insert_phased(Client $client, array $wrapped, array &$phases): array {
	$t = microtime(true);
	$endpoint = new Bulk();
	$endpoint->setBody($wrapped);
	$body = $endpoint->getBody();
	$phases['encode'] += microtime(true) - $t;

	$connection = $client->getConnectionPool()->getConnection();
	/** @var Connection $connection */
	$conn = $connection->getCurl();
	$url = 'http://' . $connection->getHost() . ':' . $connection->getPort()
		. $connection->getPath() . $endpoint->getPath();

	curl_setopt($conn, CURLOPT_URL, $url);
	curl_setopt($conn, CURLOPT_TIMEOUT, $connection->getTimeout());
	curl_setopt($conn, CURLOPT_ENCODING, '');
	curl_setopt($conn, CURLOPT_FORBID_REUSE, 0);
	curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($conn, CURLOPT_POSTFIELDS, $body);
	curl_setopt($conn, CURLOPT_CUSTOMREQUEST, $endpoint->getMethod());
	curl_setopt(
		$conn,
		CURLOPT_HTTPHEADER,
		[
			'Content-Type: ' . $endpoint->getContentType(),
		]
	);
	if ($connection->getConnectTimeout() > 0) {
		curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, $connection->getConnectTimeout());
	}

	$t = microtime(true);
	$responseString = curl_exec($conn);
	$phases['http'] += microtime(true) - $t;

	$errorno = curl_errno($conn);
	$status = curl_getinfo($conn, CURLINFO_HTTP_CODE);
	if ($errorno > 0) {
		throw new ConnectionException(curl_error($conn), $endpoint);
	}

	$t = microtime(true);
	$response = new Response($responseString, $status);
	if ($response->hasError()) {
		throw new ResponseException($endpoint, $response);
	}
	$decoded = $response->getResponse();
	$phases['decode'] += microtime(true) - $t;

	return $decoded;
}

/**
 * @return array{
 *   elapsed:float,
 *   docs:int,
 *   docs_per_sec:int,
 *   batches:int,
 *   phases:array<string,float>
 * }
 */
function bench_add_documents(Client $client, Table $table, int $docs, int $batchSize): array {
	$tableName = $table->getName();
	$phases = [
		'gen' => 0.0,
		'wrap' => 0.0,
		'encode' => 0.0,
		'http' => 0.0,
		'decode' => 0.0,
	];
	$batch = [];
	$batches = 0;
	$inserted = 0;
	$wall0 = microtime(true);
	$genStart = $wall0;

	foreach (taxi_documents($docs) as $doc) {
		$phases['gen'] += microtime(true) - $genStart;
		$batch[] = $doc;
		if (count($batch) >= $batchSize) {
			$t = microtime(true);
			$wrapped = wrap_documents_for_bulk($tableName, $batch);
			$phases['wrap'] += microtime(true) - $t;

			bulk_insert_phased($client, $wrapped, $phases);
			$inserted += count($batch);
			$batches++;
			$batch = [];
		}
		$genStart = microtime(true);
	}
	if ($batch !== []) {
		$t = microtime(true);
		$wrapped = wrap_documents_for_bulk($tableName, $batch);
		$phases['wrap'] += microtime(true) - $t;

		bulk_insert_phased($client, $wrapped, $phases);
		$inserted += count($batch);
		$batches++;
	}

	$elapsed = microtime(true) - $wall0;
	return [
		'elapsed' => $elapsed,
		'docs' => $inserted,
		'docs_per_sec' => $elapsed > 0.0 ? (int)round($inserted / $elapsed) : 0,
		'batches' => $batches,
		'phases' => $phases,
	];
}

/**
 * Times OperationNormalizer::normalize / encodeLine for streaming phase logs.
 */
final class TimingOperationNormalizer extends OperationNormalizer
{
	/** @var array<string, float> */
	private $phases;

	/**
	 * @param array<string, float> $phases
	 */
	public function __construct(?string $expectedTable, array &$phases) {
		parent::__construct($expectedTable, true);
		$this->phases = &$phases;
	}

	/**
	 * @param mixed $operation
	 * @return array{table:string,id:int,doc:array}
	 */
	public function normalize($operation): array {
		$t = microtime(true);
		$result = parent::normalize($operation);
		$this->phases['wrap'] += microtime(true) - $t;
		return $result;
	}

	public function encodeLine(array $payload): string {
		$t = microtime(true);
		$line = parent::encodeLine($payload);
		$this->phases['encode'] += microtime(true) - $t;
		return $line;
	}
}

/**
 * @param array<string, float> $phases
 * @return Generator<int, array<string, mixed>>
 */
function timed_taxi_documents(int $count, array &$phases, int $startId = 1): Generator {
	$genStart = microtime(true);
	foreach (taxi_documents($count, $startId) as $doc) {
		$phases['gen'] += microtime(true) - $genStart;
		yield $doc;
		$genStart = microtime(true);
	}
}

/**
 * @param array<string, mixed> $streamingOptions
 * @return array{
 *   elapsed:float,
 *   docs:int,
 *   docs_per_sec:int,
 *   selected_workers:?int,
 *   mode:?string,
 *   result:array,
 *   phases:array<string,float>
 * }
 */
function bench_add_documents_streaming(
	Client $client,
	Table $table,
	int $docs,
	array $streamingOptions
): array {
	$phases = [
		'gen' => 0.0,
		'wrap' => 0.0,
		'encode' => 0.0,
		'http' => 0.0,
		'decode' => 0.0,
	];
	$normalizer = new TimingOperationNormalizer($table->getName(), $phases);
	$runner = new IndexerBulk($client, null, $normalizer);
	$streamingOptions['phases'] = &$phases;

	$wall0 = microtime(true);
	$result = $runner->run(timed_taxi_documents($docs, $phases), $streamingOptions);
	$elapsed = microtime(true) - $wall0;

	$inserted = (int)($result['docs'] ?? $docs);
	return [
		'elapsed' => $elapsed,
		'docs' => $inserted,
		'docs_per_sec' => $elapsed > 0.0 ? (int)round($inserted / $elapsed) : 0,
		'selected_workers' => isset($result['selected_workers']) ? (int)$result['selected_workers'] : null,
		'mode' => isset($result['mode']) ? (string)$result['mode'] : null,
		'result' => $result,
		'phases' => $phases,
	];
}

function format_seconds(float $seconds): string {
	return sprintf('%.3f', $seconds);
}

/**
 * @param array<string, float> $phases
 */
function print_phases(array $phases, float $elapsed): void {
	$accounted = array_sum($phases);
	$other = $elapsed - $accounted;
	$labels = [
		'gen' => 'doc generation',
		'wrap' => 'addDocuments wrap',
		'encode' => 'NDJSON encode',
		'http' => 'HTTP transfer (curl_exec)',
		'decode' => 'response JSON decode',
	];
	echo "phases (wall clock breakdown):\n";
	foreach ($labels as $key => $label) {
		$seconds = $phases[$key] ?? 0.0;
		$pct = $elapsed > 0.0 ? (100.0 * $seconds / $elapsed) : 0.0;
		echo sprintf("  %-40s %8.3fs  (%5.1f%%)\n", $label . ':', $seconds, $pct);
	}
	$otherPct = $elapsed > 0.0 ? (100.0 * $other / $elapsed) : 0.0;
	echo sprintf("  %-40s %8.3fs  (%5.1f%%)\n", 'other/unaccounted:', $other, $otherPct);
	echo sprintf(
		"  client-side (gen+wrap+encode+decode): %.3fs  |  http: %.3fs\n",
		($phases['gen'] ?? 0) + ($phases['wrap'] ?? 0) + ($phases['encode'] ?? 0) + ($phases['decode'] ?? 0),
		$phases['http'] ?? 0
	);
}

/**
 * Print auto-tune probe stages from an addDocumentsStreaming result.
 *
 * @param array<string, mixed> $result
 */
function print_auto_tune_probes(array $result): void {
	$stages = $result['tuning']['stages'] ?? [];
	if (!is_array($stages) || $stages === []) {
		echo "auto-tune: skipped (fixed workers)\n";
		return;
	}

	$selected = isset($result['selected_workers']) ? (int)$result['selected_workers'] : null;
	echo "auto-tune probes:\n";
	foreach ($stages as $stage) {
		if (!is_array($stage)) {
			continue;
		}
		$workers = (int)($stage['workers'] ?? 0);
		$throughput = (float)($stage['throughput'] ?? 0.0);
		$marker = ($selected !== null && $workers === $selected) ? '  <-- selected' : '';
		echo sprintf(
			"  workers=%-4d  %.0f docs/sec%s\n",
			$workers,
			$throughput,
			$marker
		);
	}
}

function main(array $argv): int {
	try {
		$opts = parse_options($argv);
	} catch (InvalidArgumentException $e) {
		fwrite(STDERR, $e->getMessage() . "\n\n");
		print_usage();
		return 1;
	}

	if ($opts['help']) {
		print_usage();
		return 0;
	}

	$client = new Client([
		'host' => $opts['host'],
		'port' => $opts['port'],
		'transport' => 'Http',
	]);

	$streamingOptions = [];
	if ($opts['workers'] !== null) {
		$streamingOptions['workers'] = $opts['workers'];
	}
	if ($opts['max_workers'] !== null) {
		$streamingOptions['max_workers'] = $opts['max_workers'];
	}
	if ($opts['probe_docs'] !== null) {
		$streamingOptions['probe_docs'] = $opts['probe_docs'];
	}

	$workersLabel = $opts['workers'] !== null
		? (string)$opts['workers']
		: 'auto';

	echo "Target : {$opts['host']}:{$opts['port']}  table={$opts['table']}\n";
	echo "Docs   : {$opts['docs']}\n";
	echo "addDocuments batch-size={$opts['batch_size']}\n";
	echo "addDocumentsStreaming workers={$workersLabel}\n";
	echo "\n";

	echo "=== addDocuments ===\n";
	$table = recreate_table($client, $opts['table']);
	$regular = bench_add_documents($client, $table, $opts['docs'], $opts['batch_size']);
	echo "docs={$regular['docs']}  batches={$regular['batches']}  "
		. 'elapsed=' . format_seconds($regular['elapsed']) . 's  '
		. "docs_per_sec={$regular['docs_per_sec']}\n";
	print_phases($regular['phases'], $regular['elapsed']);
	echo "\n";

	echo "=== addDocumentsStreaming ===\n";
	$table = recreate_table($client, $opts['table']);
	try {
		$streaming = bench_add_documents_streaming(
			$client,
			$table,
			$opts['docs'],
			$streamingOptions
		);
	} catch (Throwable $e) {
		$table->drop(true);
		fwrite(STDERR, 'addDocumentsStreaming failed: ' . $e->getMessage() . "\n");
		return 1;
	}

	$selected = $streaming['selected_workers'] !== null
		? (string)$streaming['selected_workers']
		: 'n/a';
	$mode = $streaming['mode'] ?? 'n/a';
	echo "docs={$streaming['docs']}  mode={$mode}  selected_workers={$selected}  "
		. 'elapsed=' . format_seconds($streaming['elapsed']) . 's  '
		. "docs_per_sec={$streaming['docs_per_sec']}\n";
	print_phases($streaming['phases'], $streaming['elapsed']);
	print_auto_tune_probes($streaming['result']);
	echo "\n";

	$ratio = $regular['docs_per_sec'] > 0
		? $streaming['docs_per_sec'] / $regular['docs_per_sec']
		: 0.0;
	echo "=== summary ===\n";
	echo "addDocuments           : {$regular['docs_per_sec']} docs/sec\n";
	echo "addDocumentsStreaming  : {$streaming['docs_per_sec']} docs/sec"
		. " (x" . sprintf('%.2f', $ratio) . ")\n";

	$table->drop(true);
	return 0;
}

if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
	try {
		exit(main($argv));
	} catch (Throwable $e) {
		fwrite(STDERR, $e->getMessage() . "\n");
		exit(1);
	}
}
