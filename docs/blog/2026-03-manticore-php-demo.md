## Building a Search App with the Manticore PHP Client

We recently crafted two demos—a CLI walkthrough (`examples/demo_app.php`) and an interactive browser version (`public/demo/index.php`)—to show how little code it takes to deliver a complete search experience with Manticore Search and its PHP client. Here’s how you can follow the same path.

---

### 1. Configuring the Client

Both the CLI script and the web demo instantiate the PHP client with a simple associative array, so pointing at a different Manticore host/port is just a matter of supplying new values:

```php
$client = new \Manticoresearch\Client([
    'host' => '192.168.1.50',
    'port' => 9312,
    'transport' => 'Http',
]);
```

- **CLI**: run `php examples/demo_app.php --host=192.168.1.50 --port=9312 --transport=Http` to override the defaults.
- **Web demo**: the Host and Port fields at the top of each form feed directly into that config before every request.

If you stick with the defaults, the client talks to `127.0.0.1:9308` over HTTP.

---

### 2. Start with a Clean Real-Time Table

The client mirrors the HTTP API, and the `Table` helper keeps table lifecycle calls compact:

```php
$table = $client->table('php_client_demo');

$table->drop(true); // safe if table already exists
$table->create([
    'title' => ['type' => 'text', 'options' => ['indexed', 'stored']],
    'description' => ['type' => 'text', 'options' => ['indexed', 'stored']],
    'tags' => ['type' => 'string'],
    'price' => ['type' => 'float'],
    'views' => ['type' => 'integer'],
], [
    'min_infix_len' => 2,
    'dict' => 'keywords',
], true);
```

Those settings enable infixing and keyword dictionary mode, which are prerequisites for autocomplete and the suggestion helpers later on.

---

### 3. CRUD and Counters in Plain PHP Arrays

Every mutation is a structured array; no raw JSON is needed. With the `Table` wrapper you can insert, update, replace, and delete documents succinctly.

#### Insert sample products

```php
$table->addDocument([
    'title' => 'Wireless keyboard',
    'description' => 'Ergonomic keyboard',
    'tags' => 'peripherals',
    'price' => 59.99,
    'views' => 0,
], 1);

$table->addDocument(/* headphones */, 2);
$table->addDocument(/* desk lamp */, 3);
```

#### Increment a counter

```php
$hit = $table->getDocumentById(1);
$currentViews = $hit ? (int)($hit->getData()['views'] ?? 0) : 0;
$table->updateDocument(['views' => $currentViews + 1], 1);
```

We run this twice in the CLI demo so you can watch the counter go 0 → 1 → 2. No raw SQL—just fetch the doc, bump the value, and persist.

#### Replace and delete

```php
$table->replaceDocument([
    'title' => 'Noise cancelling headphones',
    'description' => 'Travel friendly headphones',
    'tags' => 'audio travel',
    'price' => 179.0,
    'views' => 0,
], 2);

$table->deleteDocument(3);
```

---

### 4. Search Helpers: Autocomplete & Suggestions

Because the schema enabled infixing + keyword dictionaries, you can tap into the high-level helpers immediately:

```php
$table->suggest('wirless keyboard deals', ['limit' => 3]);  // fixes first word
$table->qsuggest('add to cart keybord', ['limit' => 3]);    // fixes last word
```

For general search and fuzzy matches we use the `Search` class directly:

```php
$search = new \Manticoresearch\Search($client);

$hits = $search->setTable('php_client_demo')
    ->search('"wireless keyboard"')
    ->limit(5)
    ->get();

$fuzzyHits = $search->setTable('php_client_demo')
    ->match('wirless keybord')
    ->option('fuzzy', 1)
    ->option('layouts', 'us,ua')
    ->option('distance', 2)
    ->limit(5)
    ->get();
```

Both demos print each hit’s ID, title, score, and `views` attribute so you can see the counter reflected in search results.

---

### 5. Browser Demo: Same API, Interactive Forms

Run `php -S localhost:8080 -t public` and open `http://localhost:8080/demo/` to get a form for every example above:

- Reset table (drop + create).
- Insert, increment, replace, delete documents (with a note to seed IDs 1–3 via the insert form first).
- Autocomplete, SUGGEST, QSUGGEST forms wired to the `Table` methods.
- Search and fuzzy search using the `Search` class.

Each submission logs:

1. The PHP snippet used.
2. The request payload (friendly array form).
3. The response from Manticore, including post-update values.

It’s effectively a living cheat sheet: tweak form fields, hit submit, and copy the snippet that appears in the result panel.

---

### 6. Running Everything

1. `composer install`
2. Ensure a local Manticore Search instance is running (default HTTP port 9308—adjust the PHP client config to point elsewhere if needed).
3. CLI walkthrough: `php examples/demo_app.php`
4. Browser demo: `php -S localhost:8080 -t public` → `http://localhost:8080/demo/`

Walk through the operations in either environment and you’ll see how easily everything wires up on top of Manticore Search.

---

### Takeaway

The PHP client already exposes rich helpers (`Table`, `Search`, suggestion APIs), so you don’t need to handcraft JSON or SQL for everyday features. With a few dozen lines you get a full CRUD + search + suggestion stack that you can drop into a CLI tool, a web form, or even a background worker.
Feel free to reuse the demo scripts as boilerplate for your own app—they’re intentionally verbose with logging so you can see every request/response as you iterate.
