<?php

declare(strict_types=1);

use Manticoresearch\Client;
use Manticoresearch\Search;
use Manticoresearch\Table;

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
	fwrite(
		STDERR,
		"Composer dependencies are missing.\n".
		"Run `composer install` from the repository root before executing this demo.\n"
	);
	exit(1);
}

require $autoload;

$config = [
	'host' => getenv('MANTICORE_HOST') ?: '127.0.0.1',
	'port' => (int)(getenv('MANTICORE_PORT') ?: 9308),
	'transport' => getenv('MANTICORE_TRANSPORT') ?: 'Http',
];
$cliOptions = getopt('', ['host::', 'port::', 'transport::']);
if ($cliOptions !== false) {
	if (!empty($cliOptions['host'])) {
		$config['host'] = $cliOptions['host'];
	}
	if (!empty($cliOptions['port'])) {
		$config['port'] = (int)$cliOptions['port'];
	}
	if (!empty($cliOptions['transport'])) {
		$config['transport'] = $cliOptions['transport'];
	}
}

$client = new Client($config);
$search = new Search($client);
$tableName = 'php_client_demo';
$table = $client->table($tableName);

$dropResponse = $table->drop(true);
logStep('Reset demo table', [
	'request' => ['table' => $tableName, 'silent' => true],
	'response' => $dropResponse,
]);

$tableColumns = [
	'title' => ['type' => 'text', 'options' => ['indexed', 'stored']],
	'description' => ['type' => 'text', 'options' => ['indexed', 'stored']],
	'tags' => ['type' => 'string'],
	'price' => ['type' => 'float'],
	'views' => ['type' => 'integer'],
];
$tableSettings = [
	'min_infix_len' => 2,
	'dict' => 'keywords',
];
$createResponse = $table->create($tableColumns, $tableSettings, true);
logStep('Create RT table', [
	'request' => [
		'columns' => $tableColumns,
		'settings' => $tableSettings,
		'silent' => true,
	],
	'response' => $createResponse,
]);

$products = [
	1 => [
		'title' => 'Wireless keyboard',
		'description' => 'Ergonomic keyboard with multi-device pairing',
		'tags' => 'peripherals',
		'price' => 59.99,
		'views' => 0,
	],
	2 => [
		'title' => 'Noise cancelling headphones',
		'description' => 'Over-ear headphones that block distractions',
		'tags' => 'audio',
		'price' => 179.0,
		'views' => 0,
	],
	3 => [
		'title' => 'Desk lamp',
		'description' => 'Adjustable LED desk lamp with USB charging',
		'tags' => 'lighting',
		'price' => 39.5,
		'views' => 0,
	],
];

foreach ($products as $id => $doc) {
	$response = $table->addDocument($doc, $id);
	logStep("Insert product {$id}", [
		'request' => [
			'id' => $id,
			'doc' => $doc,
		],
		'response' => $response,
	]);
}

$incrementStep = incrementViews($table, 1);
logStep('Increment view counter on product 1', $incrementStep);

$replaceDoc = [
	'title' => 'Noise cancelling headphones',
	'description' => 'Travel friendly headphones with 30h battery',
	'tags' => 'audio travel',
	'price' => 179.0,
	'views' => 0,
];
$replaceResponse = $table->replaceDocument($replaceDoc, 2);
logStep('Replace product 2 description', [
	'request' => [
		'id' => 2,
		'doc' => $replaceDoc,
	],
	'response' => $replaceResponse,
]);

$deleteResponse = $table->deleteDocument(3);
logStep('Delete discontinued product', [
	'request' => ['id' => 3],
	'response' => $deleteResponse,
]);

$autocompleteRequest = [
	'body' => [
		'query' => 'wirel',
		'table' => $tableName,
		'options' => ['limit' => 5],
	],
];
logStep('Autocomplete "wirel"', [
	'request' => $autocompleteRequest,
	'response' => $client->autocomplete($autocompleteRequest),
]);

$phraseWithLeadingTypo = 'wirless keyboard deals';
$suggestOptions = ['limit' => 3];
$suggestResponse = $table->suggest($phraseWithLeadingTypo, $suggestOptions);
logStep(
	'SUGGEST (fix the first/leading word of the query)',
	[
		'request' => [
			'typed_phrase' => $phraseWithLeadingTypo,
			'options' => $suggestOptions,
		],
		'response' => $suggestResponse,
	]
);

$phraseWithTrailingTypo = 'add to cart keybord';
$qsuggestOptions = ['limit' => 3];
$qsuggestResponse = $table->qsuggest($phraseWithTrailingTypo, $qsuggestOptions);
logStep(
	'QSUGGEST (fix the last/trailing word of the query)',
	[
		'request' => [
			'typed_phrase' => $phraseWithTrailingTypo,
			'options' => $qsuggestOptions,
		],
		'response' => $qsuggestResponse,
	]
);

$searchQuery = '"wireless keyboard"';
displaySearchResults(
	$searchQuery,
	$search
		->setTable($tableName)
		->search($searchQuery)->limit(5)->get()
);

$fuzzyQuery = 'wirless keybord';
$fuzzySearch = new Search($client);
$fuzzyResults = $fuzzySearch
	->setTable($tableName)
	->match($fuzzyQuery)
	->option('fuzzy', 1)
	->option('layouts', 'us,ua')
	->option('distance', 2)
	->limit(5)
	->get();
displaySearchResults($fuzzyQuery . ' (fuzzy)', $fuzzyResults);

/**
 * @param string $title
 * @param mixed $payload
 */
function logStep(string $title, $payload): void {
	echo PHP_EOL, "=== {$title} ===", PHP_EOL;
	// print_r($payload);
	echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
}

/**
 * Update the views counter by selecting the current value then incrementing via UPDATE.
 *
 * @param Client $client
 * @param string $tableName
 * @param int $id
 * @return array
 */
function incrementViews(Table $table, int $id): array {
	$hit = $table->getDocumentById($id);
	$currentViews = $hit ? (int)($hit->getData()['views'] ?? 0) : 0;
	$newViews = $currentViews + 1;

	$updateResponse = $table->updateDocument(['views' => $newViews], $id);
	$updatedHit = $table->getDocumentById($id);
	$postViews = $updatedHit ? (int)($updatedHit->getData()['views'] ?? 0) : null;

	return [
		'request' => [
			'id' => $id,
			'doc' => ['views' => $newViews],
		],
		'response' => $updateResponse,
		'post_update_views' => $postViews,
	];
}

function displaySearchResults(string $query, \Manticoresearch\ResultSet $results): void {
	echo PHP_EOL, '=== Search results ===', PHP_EOL;
	echo 'Query: ' . $query . PHP_EOL;
	echo 'Total hits: ' . $results->getTotal() . PHP_EOL;
	foreach ($results as $hit) {
		/** @var \Manticoresearch\Results\ResultHit $hit */
		$hitData = $hit->getData();
		$views = $hitData['views'] ?? 'n/a';
		printf(
			" - #%d %s (score %.2f, views %s)\n",
			$hit->getId(),
			$hitData['title'] ?? '',
			$hit->getScore(),
			$views
		);
	}
	echo PHP_EOL;
}
