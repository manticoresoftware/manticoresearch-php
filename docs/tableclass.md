# Table Class

It's a wrapper on top of the Client that simplifies working with an Table.

Table provides all the operations which can be executed on a table.


```php
$config = ['host' => '127.0.0.1', 'port' => '9308'];
$client = new \Manticoresearch\Client($config);
$table = new \Manticoresearch\Table($client,'mytable');
```
The second argument is not required; the table name can also be set with `setName()`.



### setName()

Allows setting the table name.

```php
$table->setName('mytable');
```

### setCluster()

Setting the cluster name is required for add/replace/update/delete operations if the table belongs to an active cluster.

```php
$table->setCluster('mycluster');
```
### create()

Creates the table and accepts:

- fields - an array of fields where the key is the field name
- settings - an optional list of table settings
- silent - default is false; if true, no error is returned if a table with the same name already exists

Each field is an array consisting of:
- `type` - the [field/attribute type](https://manual.manticoresearch.com/Creating_a_table/Data_types)
- `options` - an array of options for the field:
  - `text` can have `indexed`, `stored` (default is both)
  - `string` can have `attribute` (default) and `indexed`
  - `json` can have `json_secondary_indexes` set to `'1'` 


Example:

```php
 $table->setName('mynewtable');
 $table->create([
    'title' => ['type' => 'text'],
    'content' => ['type' => 'text','options'=>['indexed']],
    'gid' => ['type' => 'int'],
    'label' => ['type' => 'string'],
    'tags' => ['type' => 'multi'],
    'props' => ['type' => 'json'],
    'props_indexed' => ['type' => 'json', 'options' => ['json_secondary_indexes' => '1']],
    ], [
    'rt_mem_limit' => '256M',
    'min_infix_len' => '3'
]);
```

If a setting can have multiple values, an array of values will be used, like:

```php
 $table->setName('mynewtable');
 $table->create([],
    [
        'type' => 'distributed',
        'local' => [
            'local_table_1',
            'local_table_2',
        ]
    ]
 );
````
### addDocument()

Inserts a new document into the table.
Expects:
- an array of values
- a document ID
Example:

```php
$table->addDocument([
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

Add multiple documents to the table.
Expects an array with documents as arrays.


Example:

```php
$table->addDocuments([
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

### getDocumentById()

Get an existing document by its ID.
Expects:
- a document ID
Example:

```php
$table->getDocumentById(1);
```

### getDocumentByIds()
Get multiple documents by their IDs.
Expects:
- an array of document IDs
Example:

```php
$table->getDocumentByIds([1,3,5]);
```

### replaceDocument()

Replace an existing document in the table.
Expects:
- an array of values
- a document ID
- to execute a partial replace ( `false|true`, `false` by default )

Example:

```php
$table->replaceDocument([
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

```php
$table->replaceDocument([
            'title' => 'find me',
            'label' => 'not used'
        ], 2, true);
```



### replaceDocuments()

Replace multiple documents in the table.
Expects an array with documents as arrays.


Example:

```php
$table->replaceDocuments([
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
$table->updateDocument([
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

- `table` as the table name 
- `_id` as the updated ID
- result as `updated` if the update was successful or  `noop` otherwise

Example of a return value:

```json
{"table":"test","_id":4,"result":"updated"}
```

### updateDocuments()

It can update multiple documents that match a condition.

Expects:
- an array with key pairs of attribute names and values
- a query expression - can be either as an array or as a [Query](query.md) object

An example with array:

```php
$table->updateDocuments(['price'=>100],['match'=>['*'=>'apple']]);
```

An example using a Query object:

```php
$table->updateDocuments(['_year'=>2000], new \Manticoresearch\Query\MatchQuery('team','*'));
```

```php
$bool = new BoolQuery();
$bool->must(new \Manticoresearch\Query\MatchQuery('team','*'));
$bool->must(new \Manticoresearch\Query\Range('rating',['gte'=>8.5]));
$response = $table->updateDocuments(['_year'=>2000], $bool);
```

It returns an array with:

- `table` as the table name
- updated as the number of documents updated

Example of a return value:

```json
{"table":"test","updated":2}
```

### deleteDocument()

Deletes a document. Expects one argument as the document ID.

Example:

```php
$table->deleteDocument(100);
```

It returns an array with:

- table as the table name
- _id as the document id
- found - true if document existed
- result as `deleted` if the update was successful or `not found` otherwise 

Example of a return value:

```json
{"table":"test","_id":5,"found":true,"result":"deleted"}
```

### deleteDocumentsByIds()

Deletes multiple documents by ID. Expects an array of IDs.

Example:

```php
$table->deleteDocumentsByIds([100,101]);
```

It returns an array with:

- `table` as the table name
- `_id` as the first document id passed
- `found` as true if at least one document existed
- `result` as `deleted` if at least one document was deleted, or `not found` if no document was found

Example of a return value:

```json
{"table":"test","_id":100,"found":true,"result":"deleted"}
```

### deleteDocuments()

Deletes documents using a query expression which can be passed either as an array or as a [Query](query.md) object.

An example with query as array:

```php
$table->deleteDocuments(['match'=>['*'=>'apple']]);
```

An example with query as Query object:

```php
$table->deleteDocuments( new \Manticoresearch\Query\MatchQuery('apple','*'));
```

It returns an array with:

- `table` as the table name
- `deleted` as the number of found and deleted documents

Example of a return value:

```json
{"table":"test","deleted":0}
```

### search()

It's a wrapper for the Search::search() method. It returns a [Search](searchclass.md) object instance.
It accepts either a full-text query string or a [BoolQuery](query.md#boolquery) class.
It returns a [ResultSet](searchresults.md#resultset-object) object.

```php
 $result = $table->search('find')->get();
```
Note that a new instance of the Search class is created with each call, therefore search conditions are not carried over multiple calls.
 

### drop()

Drop the table.
If `silent` is true, no error will be returned if the table doesn't exist.

```php
$table->drop($silent=false);
```
### describe()

Returns the schema of the table

```php
$table->describe();
```

### status()

Provides information about the table.

```php
$table->status();
```

### truncate()

Empties the table of data.

```php
$table->truncate();
```

### optimize()

Performs optimization on the table (not available for distributed type).

If `sync` is set to true, the command will wait for the optimization to finish, otherwise the engine will send the optimization to run in the background and return a success message.

```php
$table->optimize($sync=false);
```

### flush()

Performs Real-Time table flushing to disk.

```php
$table->flush();
```

### flushramchunk()

Performs Real-Time table flushing of the RAM chunk to disk. In general, this operation is run before performing an optimization.

```php
$table->flushramchunk();
```

### alter()

Alter the table schema. Please note that currently, the `text` type is not supported by this command.

Parameters:

- operation type - `add` or `drop`
- name of the attribute
- type of the attribute (only for `add`)


```php
$table->alter($operation,$name,$type);
```


### keywords()

Returns tokenization for an input string.

Parameters:

- input string
- options. For more information about the available options, check https://manual.manticoresearch.com/Searching/Autocomplete#CALL-KEYWORDS


```php
$table->keywords($query, $options);
```

```php
$table->alter($operation,$name,$type);
```


### suggest()

Returns suggestions for a given keyword.

Parameters:

- the keyword
- options. For more information about the available options, check https://manual.manticoresearch.com/Searching/Spell_correction#CALL-QSUGGEST,-CALL-SUGGEST

```php
$table->keywords($query, $options);
$table->suggest('trsting', ['limit' => 5]);
```


### explainQuery()

Returns the transformation tree for a full-text query without running it over the table.

Parameters:

- input query string

```php
$table->explainQuery($query);
```


### percolate()

Performs a percolate search over a percolate table. This method works only with percolate tables.

Expects an array with documents
```php
$docs = [
    ['title' => 'pick me','color'=>'blue'],
    ['title' => 'find me fast','color'=>'red'], 
    ['title' => 'something else','color'=>'blue'], 
    ['title' => 'this is false','color'=>'black']
];
$result = $table->percolate($docs);
```

Returns a [PercolateResultSet](percolateresults.md#percolateresultset-object) object containing stored queries that 
match on documents at input. The PercolateResultHit object can be iterated to retrieve the stored queries encapsulated 
as a [PercolateResultHit](percolateresults.md#percolateresulthit-object) object and the list of tables of documents 
from input.

Usage example:
```php
$docs = [['title' => 'pick me'], ['title' => 'find me fast'], ['title' => 'something else'], ['title' => 'this is false']];
$result = $table->percolate($docs);
echo "Number of stored queries with matches:".$result->count();
foreach ($result as $row) {
    echo 'Query ID' . $row->getId() . "\n";
    echo "Stored query:\n";
    print_r($row->getData());
    echo "Tables of input docs list:\n";
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
Tables of input docs list:
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
Tables of input docs list:
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
Tables of input docs list:
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
$result = $table->percolateToDocs($docs);
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