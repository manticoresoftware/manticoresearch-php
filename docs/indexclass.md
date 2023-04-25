# Index Class

It's wrapper on top of the Client that simplifies working with an Index.

Index provides all the operations which can be executed on an index.


```php
$config = ['host' => '127.0.0.1', 'port' => '9308'];
$client = new \Manticoresearch\Client($config);
$index = new \Manticoresearch\Index($client,'myindex');
```
The second argument is not required; the index name can also be set with setName().



### setName()

Allows setting the index name.

```php
$index->setName('myindex');
```

### setCluster()

Setting the cluster name is required for add/replace/update/delete operations if the index belongs to an active cluster.

```php
$index->setCluster('mycluster');
```
### create()

Creates the index and accepts:

- fields - an array of fields where the key is the field name
- settings - an optional list of index settings
- silent - default is false; if true, no error is returned if an index with the same name already exists

Each field is an array consisting of:
- `type` - the [field/attribute type](https://manual.manticoresearch.com/Creating_an_index/Data_types)
- `options` - an array of options for the field; `text` can have `indexed`, `stored` (default is both), and `string` can have `attribute` (default) and `indexed`


Example:

```php
 $index->setName('mynewindex');
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

If a setting can have multiple values, an array of values will be used, like:

```php
 $index->setName('mynewindex');
 $index->create([],
    [
        'type' => 'distributed',
        'local' => [
            'local_index_1',
            'local_index_2',
        ]
    ]
 );
````
### addDocument()

Inserts a new document into the index.
Expects:
- an array of values
- a document ID
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

### addDocuments()

Add multiple documents to the index.
Expects an array with documents as arrays.


Example:

```php
$index->addDocuments([
   [
   'id' => 1,
   'title' => 'This is an example document for cooking',
   'gid' => 1,
   'label' => 'not used',
   'tags' => [1, 2, 3],
   'props' => [
              'color' => 'blue',
              'rule' => ['one', 'two']
             ]
   ],
   [
   'id' => 2,
   'title' => 'This is another example document for cooking',
   'gid' => 100,
   'label' => 'fish',
   'tags' => [11],
   'props' => [
              'color' => 'black',
              'rule' => ['none']
             ]
   ]   
]);
```
Returns an array response with:

- errors - stating whether an error occurred
- items - response status for each document.


### replaceDocument()

Replace an existing document in the index.
Expects:
- an array of values
- a document ID

Example:

```php
$index->replaceDocument([
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

### replaceDocuments()

Replace multiple documents in the index.
Expects an array with documents as arrays.


Example:

```php
$index->replaceDocuments([
   [
   'id' => 1,
   'title' => 'This is an example document for cooking',
   'gid' => 1,
   'label' => 'not used',
   'tags' => [1, 2, 3],
   'props' => [
              'color' => 'blue',
              'rule' => ['one', 'two']
             ]
   ],
   [
   'id' => 2,
   'title' => 'This is another example document for cooking',
   'gid' => 100,
   'label' => 'fish',
   'tags' => [11],
   'props' => [
              'color' => 'black',
              'rule' => ['none']
             ]
   ]   
]);
```
Returns an array response with:

- errors - stating whether an error occurred
- items - response status for each document.

### updateDocument()

Update attributes for a given document by ID.

Expects:
- an array with key pairs of attribute names and values
- a document ID

```php
$index->updateDocument([
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

- _index as the index name
- _id as the updated ID
- result indicating whether the update was successful ('updated') or not ('noop')

```json
{"_index":"test","_id":4,"result":"updated"}
```
### updateDocuments()

It can update multiple documents that match a condition.

Expects:
- an array with key pairs of attribute names and values
- a query expression - can be either as an array or as a [Query](query.md) object

Example with array:

```php
$index->updateDocuments(['price'=>100],['match'=>['*'=>'apple']]);
```

Example using a Query object:

```php
$index->updateDocuments(['year'=>2000], new \Manticoresearch\Query\MatchQuery('team','*'));
```

```php
$bool = new BoolQuery();
$bool->must(new \Manticoresearch\Query\MatchQuery('team','*'));
$bool->must(new \Manticoresearch\Query\Range('rating',['gte'=>8.5]));
$response = $index->updateDocuments(['year'=>2000], $bool);
```

It returns an array with:

- _index as the index_name
- updated as the number of documents updated

```json
{"_index":"test","updated":2}
```

### deleteDocument()

Deletes a document. Expects one argument as the document ID.

Example:

```php
$index->deleteDocument(100);
```

It returns an array with:

- _index as index name
- _id as the document id
- found - true if document existed
- result indicating whether the update was successful ('deleted') or not ('not found')

```json
{"_index":"test","_id":5,"found":true,"result":"deleted"}
```

### deleteDocuments()

Deletes documents using a query expression which can be passed either as an array or as a [Query](query.md) object.

Example with query as array:

```php
$index->deleteDocuments(['match'=>['*'=>'apple']]);
```

Example with query as Query object:

```php
$index->deleteDocuments( new \Manticoresearch\Query\MatchQuery('apple','*'));
```

It returns an array with:

- _index as index name
- deleted as the number of found and deleted documents

```json
{"_index":"test","deleted":0}
```

### search()

It's a wrapper for the Search::search() method. It returns a [Search](searchclass.md) object instance.
It accepts either a full-text query string or a [BoolQuery](query.md#boolquery) class.
It returns a [ResultSet](searchresults.md#resultset-object) object.

```php
 $result = $index->search('find')->get();
```
Note that a new instance of the Search class is created with each call, therefore search conditions are not carried over multiple calls.
 

### drop()

Drop the index.
If `silent` is true, no error will be returned if the index doesn't exist.

```php
$index->drop($silent=false);
```
### describe()

Returns the schema of the index

```php
$index->describe();
```

### status()

Provides information about the index.

```php
$index->status();
```

### truncate()

Empties the index of data.

```php
$index->truncate();
```

### optimize()

Performs optimization on the index (not available for distributed type).

If `sync` is set to true, the command will wait for the optimization to finish, otherwise the engine will send the optimization to run in the background and return a success message.

```php
$index->optimize($sync=false);
```

### flush()

Performs Real-Time index flushing to disk.

```php
$index->flush();
```

### flushramchunk()

Performs Real-Time index flushing of the RAM chunk to disk. In general, this operation is run before performing an optimization.

```php
$index->flushramchunk();
```

### alter()

Alter the index schema. Please note that currently, the `text` type is not supported by this command.

Parameters:

- operation type - `add` or `drop`
- name of the attribute
- type of the attribute (only for `add`)


```php
$index->alter($operation,$name,$type);
```


### keywords()

Returns tokenization for an input string.

Parameters:

- input string
- options. For more information about the available options, check https://manual.manticoresearch.com/Searching/Autocomplete#CALL-KEYWORDS


```php
$index->keywords($query, $options);
```

```php
$index->alter($operation,$name,$type);
```


### suggest()

Returns suggestions for a given keyword.

Parameters:

- the keyword
- options. For more information about the available options, check https://manual.manticoresearch.com/Searching/Spell_correction#CALL-QSUGGEST,-CALL-SUGGEST

```php
$index->keywords($query, $options);
$index->suggest('trsting', ['limit' => 5]);
```


### explainQuery()

Returns the transformation tree for a full-text query without running it over the index.

Parameters:

- input query string

```php
$index->explainQuery($query);
```


### percolate()

Performs a percolate search over a percolate index. This method works only with percolate indexes.

Expects an array with documents
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
match on documents at input. The PercolateResultHit object can be iterated to retrieve the stored queries encapsulated 
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

It performs a percolate query just like the [percolate()](#percolate) method, but instead of returning an object list
with stored queries and matched input documents attached, it returns an object list with the 
input documents and the stored queries they match against.

The returned iterator is a [PercolateDocsResultSet](percolateresults.md#percolatedocsresultset-object) object which holds a 
list of [PercolateResultDoc](percolateresults.md#percolateresultdoc-object) objects. 
The PercolateResultDoc provides the document through the `getData()` method and a list of queries through the `getQueries()` method.

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
<!-- proofread -->