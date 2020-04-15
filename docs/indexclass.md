# Index Class

It's wrapper on Client and Search classes that allows to create easier operations on indexes.

```php
$config = ['host' => '127.0.0.1', 'port' => '9308'];
$client = new Client($config);
$index = new Index($client,'myindex');
```
Second argument is not required, the index name can be also set with setName().



### setName()

Allows setting the index name.

```php
$index->setName('myindex');
```

### create()

Creates the index, accepts:

- fields - array with columns
- settings - optional list of index settings


```php
 $index->create(['title' => ['type' => 'text'], 'gid' => ['type' => 'int'], 'label' => ['type' => 'string'], 'tags' => ['type' => 'multi'], 'props' => ['type' => 'json']], []);
```

### addDocument()

Inserts a new document in the index.
Expects:
- array of values
- document id

```php
$index->addDocument([
            'title' => 'find me',
            'gid' => 1,
            'label' => 'not used',
            'tags' => [1, 2, 3],
            'props' => [
                'color' => 'blue',
                'rule' => ['one', 'two']
            ]
        ], 1);
```

### replaceDocument()

Replace an existing document in the index.
Expects:
- array of values
- document id

```php
$index->addDocument([
            'title' => 'find me',
            'gid' => 1,
            'label' => 'not used',
            'tags' => [1, 2, 3],
            'props' => [
                'color' => 'blue',
                'rule' => ['one', 'two']
            ]
        ], 1);
```

### deleteDocument()

Deletes a document. Expects one argument with the document id.

```php
$index-deleteDocument(100);
```

### deleteDocuments()

Deletes documents using a query expression

```php
$index-deleteDocuments(['match'=>['*'=>'apple']]);
```

### search()

It's a wrapper to a Search::search(). It return a Search object.
```php
 $result = $index->search('find')->get();
```