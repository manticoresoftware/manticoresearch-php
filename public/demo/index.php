<?php

declare(strict_types=1);

use Manticoresearch\Client;
use Manticoresearch\Search;

$autoload = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($autoload)) {
	die('Run "composer install" in the project root before using the demo.');
}
require $autoload;

function snippetFor(string $action): string {
	static $map = [
		'reset_table' => <<<PHP
\$client = new \Manticoresearch\Client([
    'host' => '{host}',
    'port' => {port},
]);
\$table = \$client->table(\$tableName);
\$table->drop(true);
\$table->create([
    'title' => ['type' => 'text', 'options' => ['indexed', 'stored']],
    'description' => ['type' => 'text', 'options' => ['indexed', 'stored']],
    'tags' => ['type' => 'string'],
    'price' => ['type' => 'float'],
    'views' => ['type' => 'integer'],
], [
    'min_infix_len' => 2,
    'dict' => 'keywords',
], true);
PHP,
		'insert_doc' => <<<PHP
\$client = new \Manticoresearch\Client([
    'host' => '{host}',
    'port' => {port},
]);
\$table = \$client->table(\$tableName);
\$table->addDocument([
    'title' => 'Wireless keyboard',
    'description' => 'Ergonomic keyboard',
    'tags' => 'peripherals',
    'price' => 59.99,
    'views' => 0,
], \$id);
PHP,
		'increment_views' => <<<PHP
\$client = new \Manticoresearch\Client([
    'host' => '{host}',
    'port' => {port},
]);
\$table = \$client->table(\$tableName);
\$doc = \$table->getDocumentById(\$id);
\$currentViews = \$doc ? (\$doc->getData()['views'] ?? 0) : 0;
\$table->updateDocument(['views' => \$currentViews + 1], \$id);
PHP,
		'replace_doc' => <<<PHP
\$client = new \Manticoresearch\Client([
    'host' => '{host}',
    'port' => {port},
]);
\$table = \$client->table(\$tableName);
\$table->replaceDocument([
    'title' => 'Noise cancelling headphones',
    'description' => 'Travel friendly headphones',
    'tags' => 'audio travel',
    'price' => 179.0,
    'views' => 0,
], \$id);
PHP,
		'delete_doc' => <<<PHP
\$client = new \Manticoresearch\Client([
    'host' => '{host}',
    'port' => {port},
]);
\$table = \$client->table(\$tableName);
\$table->deleteDocument(\$id);
PHP,
		'autocomplete' => <<<PHP
\$client = new \Manticoresearch\Client([
    'host' => '{host}',
    'port' => {port},
]);
\$client->autocomplete([
    'body' => [
        'query' => 'wirel',
        'table' => \$tableName,
        'options' => ['limit' => 5],
    ],
]);
PHP,
		'suggest' => <<<PHP
\$client = new \Manticoresearch\Client([
    'host' => '{host}',
    'port' => {port},
]);
\$table = \$client->table(\$tableName);
\$table->suggest('wirless keyboard deals', ['limit' => 3]);
PHP,
		'qsuggest' => <<<PHP
\$client = new \Manticoresearch\Client([
    'host' => '{host}',
    'port' => {port},
]);
\$table = \$client->table(\$tableName);
\$table->qsuggest('add to cart keybord', ['limit' => 3]);
PHP,
		'search' => <<<PHP
\$client = new \Manticoresearch\Client([
    'host' => '{host}',
    'port' => {port},
]);
\$search = new \Manticoresearch\Search(\$client);
\$results = \$search->setTable(\$tableName)->search('"wireless keyboard"')->limit(5)->get();
foreach (\$results as \$hit) {
    // ...
}
PHP,
		'fuzzy_search' => <<<PHP
\$search = new \Manticoresearch\Search(\$client);
\$results = \$search->setTable(\$tableName)
    ->match('wirless keybord')
    ->option('fuzzy', 1)
    ->option('layouts', 'us,ua')
    ->option('distance', 2)
    ->limit(5)
    ->get();
PHP,
	];

	return $map[$action] ?? '';
}

$host = $_POST['host'] ?? '127.0.0.1';
$port = (int)($_POST['port'] ?? 9308);
$tableName = $_POST['table'] ?? 'php_client_demo';
$action = $_POST['action'] ?? '';

$lastResult = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		$client = new Client([
			'host' => $host,
			'port' => $port,
			'transport' => 'Http',
		]);
		$table = $client->table($tableName);

		switch ($action) {
		case 'reset_table':
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
			$dropResponse = $table->drop(true);
			$createResponse = $table->create($tableColumns, $tableSettings, true);
			$lastResult = [
				'title' => 'Reset demo table',
				'request' => [
					'drop' => ['table' => $tableName, 'silent' => true],
					'create' => [
						'columns' => $tableColumns,
						'settings' => $tableSettings,
						'silent' => true,
					],
				],
				'response' => [
					'drop' => $dropResponse,
					'create' => $createResponse,
				],
				'code' => snippetFor($action),
			];
			break;

		case 'insert_doc':
			$insertRequest = [
				'id' => (int)($_POST['doc_id'] ?? 0),
				'doc' => [
					'title' => trim($_POST['title'] ?? ''),
					'description' => trim($_POST['description'] ?? ''),
					'tags' => trim($_POST['tags'] ?? ''),
					'price' => (float)($_POST['price'] ?? 0),
					'views' => (int)($_POST['views'] ?? 0),
				],
			];
			$lastResult = [
				'title' => 'Insert document',
				'request' => $insertRequest,
				'response' => $table->addDocument($insertRequest['doc'], $insertRequest['id']),
				'code' => snippetFor($action),
			];
			break;

		case 'increment_views':
			$docId = (int)($_POST['views_id'] ?? 0);
			$hit = $table->getDocumentById($docId);
			$currentViews = $hit ? (int)($hit->getData()['views'] ?? 0) : 0;
			$newViews = $currentViews + 1;
			$updateResponse = $table->updateDocument(['views' => $newViews], $docId);
			$updatedHit = $table->getDocumentById($docId);
			$postViews = $updatedHit ? (int)($updatedHit->getData()['views'] ?? 0) : null;
			$lastResult = [
				'title' => 'Increment views counter',
				'request' => [
					'id' => $docId,
					'doc' => ['views' => $newViews],
				],
				'response' => [
					'update' => $updateResponse,
					'post_update_views' => $postViews,
				],
				'code' => snippetFor($action),
			];
			break;

		case 'replace_doc':
			$replaceRequest = [
				'id' => (int)($_POST['replace_id'] ?? 0),
				'doc' => [
					'title' => trim($_POST['replace_title'] ?? ''),
					'description' => trim($_POST['replace_description'] ?? ''),
					'tags' => trim($_POST['replace_tags'] ?? ''),
					'price' => (float)($_POST['replace_price'] ?? 0),
					'views' => (int)($_POST['replace_views'] ?? 0),
				],
			];
			$lastResult = [
				'title' => 'Replace document',
				'request' => $replaceRequest,
				'response' => $table->replaceDocument($replaceRequest['doc'], $replaceRequest['id']),
				'code' => snippetFor($action),
			];
			break;

		case 'delete_doc':
			$deleteId = (int)($_POST['delete_id'] ?? 0);
			$lastResult = [
				'title' => 'Delete document',
				'request' => ['id' => $deleteId],
				'response' => $table->deleteDocument($deleteId),
				'code' => snippetFor($action),
			];
			break;

		case 'autocomplete':
			$autocompleteRequest = [
				'body' => [
					'query' => trim($_POST['autocomplete_query'] ?? ''),
					'table' => $tableName,
					'options' => ['limit' => (int)($_POST['autocomplete_limit'] ?? 5)],
				],
			];
			$lastResult = [
				'title' => 'Autocomplete',
				'request' => $autocompleteRequest,
				'response' => $client->autocomplete($autocompleteRequest),
				'code' => snippetFor($action),
			];
			break;

		case 'suggest':
			$suggestRequest = [
				'phrase' => trim($_POST['suggest_query'] ?? ''),
				'options' => ['limit' => (int)($_POST['suggest_limit'] ?? 3)],
			];
			$lastResult = [
				'title' => 'SUGGEST (first word)',
				'request' => $suggestRequest,
				'response' => $table->suggest($suggestRequest['phrase'], $suggestRequest['options']),
				'code' => snippetFor($action),
			];
			break;

		case 'qsuggest':
			$qsuggestRequest = [
				'phrase' => trim($_POST['qsuggest_query'] ?? ''),
				'options' => ['limit' => (int)($_POST['qsuggest_limit'] ?? 3)],
			];
			$lastResult = [
				'title' => 'QSUGGEST (last word)',
				'request' => $qsuggestRequest,
				'response' => $table->qsuggest($qsuggestRequest['phrase'], $qsuggestRequest['options']),
				'code' => snippetFor($action),
			];
			break;

		case 'search':
			$searchQuery = trim($_POST['search_query'] ?? '');
			$limit = (int)($_POST['search_limit'] ?? 5);
			$search = new Search($client);
			$resultSet = $search->setTable($tableName)->search($searchQuery)->limit($limit)->get();
			$hits = [];
			foreach ($resultSet as $hit) {
				$hits[] = [
					'id' => $hit->getId(),
					'score' => $hit->getScore(),
					'data' => $hit->getData(),
				];
			}
			$lastResult = [
				'title' => 'Search',
				'request' => ['query' => $searchQuery, 'limit' => $limit],
				'response' => [
					'total' => $resultSet->getTotal(),
					'hits' => $hits,
				],
				'code' => snippetFor($action),
			];
			break;

		case 'fuzzy_search':
			$fuzzyQuery = trim($_POST['fuzzy_query'] ?? '');
			$fuzzyLimit = (int)($_POST['fuzzy_limit'] ?? 5);
			$fuzzyLayouts = trim($_POST['fuzzy_layouts'] ?? 'us,ua');
			$fuzzyDistance = (int)($_POST['fuzzy_distance'] ?? 2);
			$fuzzySearch = new Search($client);
			$fuzzySearch->setTable($tableName)
				->match($fuzzyQuery)
				->option('fuzzy', 1);
			if ($fuzzyLayouts !== '') {
				$fuzzySearch->option('layouts', $fuzzyLayouts);
			}
			if ($fuzzyDistance > 0) {
				$fuzzySearch->option('distance', $fuzzyDistance);
			}
			$fuzzyResultSet = $fuzzySearch->limit($fuzzyLimit)->get();
			$fuzzyHits = [];
			foreach ($fuzzyResultSet as $hit) {
				$fuzzyHits[] = [
					'id' => $hit->getId(),
					'score' => $hit->getScore(),
					'data' => $hit->getData(),
				];
			}
			$lastResult = [
				'title' => 'Fuzzy search',
				'request' => [
					'query' => $fuzzyQuery,
					'limit' => $fuzzyLimit,
					'layouts' => $fuzzyLayouts,
					'distance' => $fuzzyDistance,
				],
				'response' => [
					'total' => $fuzzyResultSet->getTotal(),
					'hits' => $fuzzyHits,
				],
				'code' => snippetFor($action),
			];
			break;

		}
	} catch (Throwable $e) {
		$errorMessage = $e->getMessage();
		$lastResult = [
			'title' => 'Error',
			'request' => ['action' => $action],
			'response' => [
				'type' => get_class($e),
				'message' => $e->getMessage(),
			],
			'code' => snippetFor($action),
		];
	}
}

function renderConnectionFields(string $host, int $port, string $table): string {
	$hostEsc = htmlspecialchars($host, ENT_QUOTES);
	$tableEsc = htmlspecialchars($table, ENT_QUOTES);
	return <<<HTML
	<label>Host
		<input type="text" name="host" value="{$hostEsc}">
	</label>
	<label>Port
		<input type="number" name="port" value="{$port}">
	</label>
	<label>Table
		<input type="text" name="table" value="{$tableEsc}">
	</label>
HTML;
}

function encodeJson($data): string {
	return htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_NOQUOTES);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Manticore PHP Client Demo</title>
	<style>
		body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f6f8; color: #222; }
		h1 { margin-top: 0; }
		section { background: #fff; padding: 16px; border-radius: 8px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
		fieldset { border: 1px solid #ddd; padding: 12px; margin-bottom: 12px; }
		label { display: inline-flex; flex-direction: column; margin-right: 12px; font-size: 0.9rem; }
		input[type="text"], input[type="number"], textarea { padding: 6px; min-width: 220px; }
		textarea { min-height: 80px; resize: vertical; }
		button { padding: 8px 16px; border: none; border-radius: 4px; background: #0070f3; color: #fff; cursor: pointer; }
		button:hover { background: #0059c1; }
		.result pre { background: #0d1117; color: #e6edf3; padding: 12px; border-radius: 8px; overflow-x: auto; }
		.error { color: #c62828; font-weight: bold; }
		.forms-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; }
		.toggle-btn { background: #eef4ff; border: 1px solid #c7d8ff; border-radius: 4px; color: #0059c1; cursor: pointer; font-size: 0.9rem; margin-bottom: 8px; padding: 4px 8px; }
		.toggle-btn:hover { background: #d9e7ff; text-decoration: underline; }
	</style>
	<script>
		function toggleDetails(id) {
			var el = document.getElementById(id);
			if (!el) return;
			el.hidden = !el.hidden;
		}
	</script>
</head>
<body>
	<h1>Manticore PHP Client – Interactive Demo</h1>
	<p>Use these forms to run basic operations with your data using Manticore PHP client. Start by resetting the demo table, then insert and manipulate documents, and finally explore search and suggestion helpers.</p>

	<?php if ($errorMessage): ?>
		<section class="error">Error: <?= htmlspecialchars($errorMessage, ENT_QUOTES) ?></section>
	<?php endif; ?>

	<?php if ($lastResult): ?>
		<section class="result">
			<h2>Last operation: <?= htmlspecialchars($lastResult['title'], ENT_QUOTES) ?></h2>
			<button type="button" class="toggle-btn" onclick="toggleDetails('result-details')">
				Toggle PHP/Request/Response
			</button>
			<div id="result-details">
				<?php if (!empty($lastResult['code'])): ?>
					<h3>PHP code</h3>
					<pre><?= htmlspecialchars(str_replace(
						['{host}', '{port}'],
						[$host, (string)$port],
						$lastResult['code']
					), ENT_NOQUOTES) ?></pre>
				<?php endif; ?>
				<h3>Request</h3>
				<pre><?= encodeJson($lastResult['request']) ?></pre>
				<h3>Response</h3>
				<pre><?= encodeJson($lastResult['response']) ?></pre>
			</div>
		</section>
	<?php endif; ?>

	<section>
		<h2>1. Table Management</h2>
		<form method="post">
			<input type="hidden" name="action" value="reset_table">
			<fieldset>
				<legend>Reset (drop + create) RT table</legend>
				<?= renderConnectionFields($host, $port, $tableName) ?>
				<p><button type="submit">Reset Table</button></p>
			</fieldset>
		</form>
	</section>

	<section>
		<h2>2. Document Operations</h2>
		<p style="font-size:0.9rem;color:#555;">
			Tip: use the insert form to seed IDs 1, 2, and 3 (update the “Document ID” field accordingly) before trying the update/replace/delete examples.
		</p>
		<div class="forms-grid">
			<form method="post">
				<input type="hidden" name="action" value="insert_doc">
				<fieldset>
					<legend>Insert Document</legend>
					<?= renderConnectionFields($host, $port, $tableName) ?>
					<label>Document ID <input type="number" name="doc_id" value="1"></label>
					<label>Title <input type="text" name="title" value="Wireless keyboard"></label>
					<label>Description <textarea name="description">Ergonomic keyboard</textarea></label>
					<label>Tags <input type="text" name="tags" value="peripherals"></label>
					<label>Price <input type="number" step="0.01" name="price" value="59.99"></label>
					<label>Views <input type="number" name="views" value="0"></label>
					<p><button type="submit">Insert</button></p>
				</fieldset>
			</form>

			<form method="post">
				<input type="hidden" name="action" value="increment_views">
				<fieldset>
					<legend>Update (increment views)</legend>
					<?= renderConnectionFields($host, $port, $tableName) ?>
					<label>Document ID <input type="number" name="views_id" value="1"></label>
					<p><button type="submit">Increment Views</button></p>
				</fieldset>
			</form>

			<form method="post">
				<input type="hidden" name="action" value="replace_doc">
				<fieldset>
					<legend>Replace Document</legend>
					<?= renderConnectionFields($host, $port, $tableName) ?>
					<label>Document ID <input type="number" name="replace_id" value="2"></label>
					<label>Title <input type="text" name="replace_title" value="Noise cancelling headphones"></label>
					<label>Description <textarea name="replace_description">Travel friendly headphones</textarea></label>
					<label>Tags <input type="text" name="replace_tags" value="audio travel"></label>
					<label>Price <input type="number" step="0.01" name="replace_price" value="179.00"></label>
					<label>Views <input type="number" name="replace_views" value="0"></label>
					<p><button type="submit">Replace</button></p>
				</fieldset>
			</form>

			<form method="post">
				<input type="hidden" name="action" value="delete_doc">
				<fieldset>
					<legend>Delete Document</legend>
					<?= renderConnectionFields($host, $port, $tableName) ?>
					<label>Document ID <input type="number" name="delete_id" value="3"></label>
					<p><button type="submit">Delete</button></p>
				</fieldset>
			</form>
		</div>
	</section>

	<section>
		<h2>3. Search Helpers</h2>
		<div class="forms-grid">
			<form method="post">
				<input type="hidden" name="action" value="autocomplete">
				<fieldset>
					<legend>Autocomplete</legend>
					<?= renderConnectionFields($host, $port, $tableName) ?>
					<label>Query fragment <input type="text" name="autocomplete_query" value="wirel"></label>
					<label>Limit <input type="number" name="autocomplete_limit" value="5"></label>
					<p><button type="submit">Call AUTOCOMPLETE</button></p>
				</fieldset>
			</form>

			<form method="post">
				<input type="hidden" name="action" value="suggest">
				<fieldset>
					<legend>SUGGEST (first word)</legend>
					<?= renderConnectionFields($host, $port, $tableName) ?>
					<label>Phrase <input type="text" name="suggest_query" value="wirless keyboard deals"></label>
					<label>Limit <input type="number" name="suggest_limit" value="3"></label>
					<p><button type="submit">Call SUGGEST</button></p>
				</fieldset>
			</form>

			<form method="post">
				<input type="hidden" name="action" value="qsuggest">
				<fieldset>
					<legend>QSUGGEST (last word)</legend>
					<?= renderConnectionFields($host, $port, $tableName) ?>
					<label>Phrase <input type="text" name="qsuggest_query" value="add to cart keybord"></label>
					<label>Limit <input type="number" name="qsuggest_limit" value="3"></label>
					<p><button type="submit">Call QSUGGEST</button></p>
				</fieldset>
			</form>
		</div>
	</section>

	<section>
		<h2>4. Search</h2>
		<div class="forms-grid">
			<form method="post">
				<input type="hidden" name="action" value="search">
				<fieldset>
					<legend>Full-text search</legend>
					<?= renderConnectionFields($host, $port, $tableName) ?>
					<label>Query <input type="text" name="search_query" value='"wireless keyboard"'></label>
					<label>Limit <input type="number" name="search_limit" value="5"></label>
					<p><button type="submit">Search</button></p>
				</fieldset>
			</form>

			<form method="post">
				<input type="hidden" name="action" value="fuzzy_search">
				<fieldset>
					<legend>Fuzzy search</legend>
					<?= renderConnectionFields($host, $port, $tableName) ?>
					<label>Query <input type="text" name="fuzzy_query" value="wirless keybord"></label>
					<label>Limit <input type="number" name="fuzzy_limit" value="5"></label>
					<label>Layouts <input type="text" name="fuzzy_layouts" value="us,ua"></label>
					<label>Distance <input type="number" name="fuzzy_distance" value="2"></label>
					<p><button type="submit">Search (fuzzy)</button></p>
				</fieldset>
			</form>
		</div>
	</section>

	<p style="font-size: 0.9rem; color: #555;">Tip: start the server with <code>php -S localhost:8080 -t public</code> and open <code>http://localhost:8080/demo/</code>.</p>
</body>
</html>
