# Index Class

It's wrapper on top of the Client that simplifies working with an Index.

Index provides all the operations which can be executed on an index.


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

- fields - array of the fields where key is the field name
- settings - optional list of index settings
- silent - default is false, if true, no error is returned if an index with same name already exists

Each field is an array consisting of:
- `type` -  the field/attribute type
- `options` -  an array of options of the field, currently only `text` can have `indexed`,`stored` (default is both)

Example:

```php
 $index->create([
    'title' => ['type' => 'text'],
    'content' => ['type' => 'text','options'=>['indexed']],
    'gid' => ['type' => 'int'],
    'label' => ['type' => 'string'],
    'tags' => ['type' => 'multi'],
    'props' => ['type' => 'json']
    ], [
    'rt_mem_limit' => '256M',
    'min_infix_len' => '3'
]);
```

### addDocument()

Inserts a new document in the index.
Expects:
- array of values
- document id
Example:

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

Example:

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

### updateDocument()

Update attributes for a given document by Id.

Expects:
-  array with key pairs of attribute names and values
-  document id

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

It returns an array with:

- _index as index name
- _id as the id updated
- result whenever update was successful ('updated') or not ('noop')

```json
{"_index":"test","_id":4,"result":"updated"}
```
### updateDocuments()

It can update multiple documents that match a condition.

Expects:
-  array with key pairs of attribute names and values
-  query expression - can be either as array or as [Query](query.md) object

Example with array:

```php
$index->updateDocuments(['price'=>100],['match'=>['*'=>'apple']]);
```

Example with Query object:

```php
$index->updateDocuments(['year'=>2000], new Match('team','*'));
```

```php
$bool = new BoolQuery();
$bool->must(new Match('team','*'));
$bool->must(new Range('rating',['gte'=>8.5]));
$response = $index->updateDocuments(['year'=>2000], $bool);
```

It returns an array with:

- _index as index_name
- updated  as number of documents updated

```json
{"_index":"test","updated":2}
```

### deleteDocument()

Deletes a document. Expects one argument as the document id.

Example:

```php
$index->deleteDocument(100);
```

It returns an array with:

- _index as index name
- _id as the document id
- found - true if document existed
- result whenever update was successful ('deleted') or not ('not found')

```json
{"_index":"test","_id":5,"found":true,"result":"deleted"}
```

### deleteDocuments()

Deletes documents using a query expression which can be passed either as array or as [Query](query.md) object.

Example with query as array:

```php
$index->deleteDocuments(['match'=>['*'=>'apple']]);
```

Example with query as Query object:

```php
$index->deleteDocuments( new Match('apple','*'));
```

It returns an array with:

- _index as index name
- deleted as number of found documents and deleted

```json
{"_index":"test","deleted":0}
```

### search()

It's a wrapper to a Search::search(). It return a [Search](searchclass.md) object instance.
It accept either a full-text query string or a [BoolQuery](query.md#boolquery) class.
It returns a [ResultSet](searchresults.md#resultset-object) object.

```php
 $result = $index->search('find')->get();
```
Note that on every call a new instance of Search class is created, therefor search conditions are not carried over multiple calls.
 

### drop()

Drop the index.
If `silent` is true, no error will be returned if the index doesn't exists.

```php
$index->drop($silent=false);
```
### describe()

Returns schema of the index

```php
$index->describe();
```

### status()

Provides information about the index.

```php
$index->status();
```

### truncate()

Empty the index of data.

```php
$index->truncate();
```

### optimize()

Performs optimization on index (not available for distributed type).

If `sync` is set to true, the command will wait for optimize to finish, otherwise the engine will sent the optimize in background and return success message back.

```php
$index->optimize($sync=false);
```

### flush()

Performs Real-Time index flushing to disk.

```php
$index->flush();
```

### flushramchunk()

Performs Real-Time index flushing of the RAM chunk to disk. In general this operation is run before doing an optimize.

```php
$index->flushramchunk();
```

### alter()

Alter index schema. Please note that currently `text` type is not supported by this command.

Parameters:

- operation type -  `add` or `drop`
- name of the attribute 
- type of the attribute (only for `add`)


```php
$index->alter($operation,$name,$type);
```


### keywords()

Returns tokenization for an input string.

Parameters:

- input string
- options. For more information about the available options check https://docs.manticoresearch.com/latest/html/sphinxql_reference/call_keywords_syntax.html


```php
$index->keywords($query, $options);
```

```php
$index->alter($operation,$name,$type);
```


### suggest()

Returns suggestions for a give keyword.

Parameters:

- the keyword
- options. For more information about the available options check https://docs.manticoresearch.com/latest/html/sphinxql_reference/call_qsuggest_syntax.html

```php
$index->keywords($query, $options);
$index->suggest('trsting', ['limit' => 5]);
```


### explainQuery()

Returns transformation tree for a full-text query without running it over the index.

Parameters:

- input query string

```php
$index->explainQuery($query);
```