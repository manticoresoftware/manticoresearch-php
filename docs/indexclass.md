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

### setCluster()

Setting the cluster name is required for add/replace/update/delete operations if the index belongs to an
 active cluster. 

```php
$index->setCluster('mycluster');
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


### percolate()

Performs a percolate search over a percolate index. This method works only with percolate indexes.

Expects an array  with documents
```php
$docs = [
    ['title' => 'pick me','color'=>'blue'],
    ['title' => 'find me fast','color'=>'red'], 
    ['title' => 'something else','color'=>'blue'], 
    ['title' => 'this is false','color'=>'black']
];
$result = $index->percolate($docs);
```

Returns a [PercolateResultSet](percolateresults.md#percolateresultset-object) object containing stored queries that 
match on documents at input.  The PercolateResultHit object can be iterated to retrieve the stored queries encapsulated 
as a [PercolateResultHit](percolateresults.md#percolateresulthit-object) object and the list of indices of documents 
from input.

Usage example:
```php
$docs = [['title' => 'pick me'], ['title' => 'find me fast'], ['title' => 'something else'], ['title' => 'this is false']];
$result = $index->percolate($docs);
echo "Number of stored queries with matches:".$result->count();
foreach ($result as $row) {
    echo 'Query ID' . $row->getId() . "\n";
    echo "Stored query:\n";
    print_r($row->getData());
    echo "Indices of input docs list:\n";
    print_r($row->getDocSlots());
    echo "List of input docs that match:\n";
    print_r($row->getDocsMatched($docs));
}
```
And response:
```php
Number of stored queries with matches: 3
Query ID6
Stored query:
Array
(
    [query] => Array
        (
            [ql] => find me
        )
)
Indices of input docs list:
Array
(
    [0] => 2
    [1] => 4
)
List of input docs that match:
Array
(
    [0] => Array
        (
            [title] => find me fast
        )

    [1] => Array
        (
            [title] => find me slow
        )
)
Query ID7
Stored query:
Array
(
    [query] => Array
        (
            [ql] => something
        )
)
Indices of input docs list:
Array
(
    [0] => 3
)
List of input docs that match:
Array
(
    [0] => Array
        (
            [title] => something else
        )
)
Query ID8
Stored query:
Array
(
    [query] => Array
        (
            [ql] => fast
        )

)
Indices of input docs list:
Array
(
    [0] => 2
)
List of input docs that match:
Array
(
    [0] => Array
        (
            [title] => find me fast
        )
)
```
### percolateToDocs()

It performs a percolate query just like [percolate()](#percolate) method but instead of returning an object list
with stored queries and matched input documents attached, it returns instead an object list with the 
input documents and the stored queries they match against.

The returned iterator is an [PercolateDocsResultSet](percolateresults.md#percolatedocsresultset-object) object which holds a 
list of [PercolateResultDoc](percolateresults.md#percolateresultdoc-object) objects. 
The PercolateResultDoc provides the document by `getData()` method and a list of queries by `getQueries()` method.

`getQueries()` returns an array with [PercolateResultHit](percolateresults.md#percolateresulthit-object) objects.

```php
$docs = [['title' => 'pick me'], ['title' => 'find me fast'], ['title' => 'something else'], ['title' => 'this is false']];
$result = $index->percolateToDocs($docs);
foreach ($result as $row) {
    echo "Document:\n";
    print_r($row->getData());
    echo "Matched queries:\n";
    foreach($row->getQueries()  as $query) {
        print_r($query->getData());
    }
}
```
```
Document:
Array
(
    [title] => pick me
)
Matched queries:
Document:
Array
(
    [title] => find me fast
)
Matched queries:
Array
(
    [query] => Array
        (
            [ql] => find me
        )
)
Array
(
    [query] => Array
        (
            [ql] => fast
        )
)
Document:
Array
(
    [title] => something else
)
Matched queries:
Array
(
    [query] => Array
        (
            [ql] => something
        )
)
Document:
Array
(
    [title] => find me slow
)
Matched queries:
Array
(
    [query] => Array
        (
            [ql] => find me
        )
)
```