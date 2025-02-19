Low-level client calls
======================

The [Search](searchclass.md) and [Table](tableclass.md) classes are built on top of the low-level Client class methods,
providing an easier way to construct the queries. 
Those familiar with the HTTP API syntax or wanting the ultimate speed can simply use the Client class.

The methods of the Client class reflect, as much as possible, the same request payloads/responses of the Manticore Search HTTP API.

Besides operations on a table, there are also provided methods for performing tasks on the search server or search cluster.

Table of Contents
-----------------

* [General notes on requests](#requests) 

* [Search](#search)

* [Insert documents](#insert)

* [Update documents](#update)

* [Replace documents](#replace)

* [Delete documents](#delete)

* [Bulk operations with documents](#bulk)

* [Percolate searches](percolate.md)

* [Query helpers](queryhelpers.md)

* Administrative operations

    * [Tables](tables.md)
    * [Nodes](nodes.md)
    * [Cluster](cluster.md)
    
* [Running SQL](sql.md)


### Requests

Each request array can have one of the following parameters:

* body - its content goes as the payload of the HTTP request
* table/cluster - table/cluster name
* id - document id
* query - endpoint URL parameters (not to be confused with `query` found in the payload of some responses)

Depending on the request, some of the parameters are mandatory.

There is no check regarding the validity of the body payload's structure before sending the request.


### Responses 

Responses are returned as arrays, reflecting the response object received from the API endpoint.

### Search
For a complete reference of payload and response, see Manticore's [Search API](https://manual.manticoresearch.com/Searching/Full_text_matching/Basic_usage#HTTP-JSON).

`body` properties:
- table name (mandatory)
- query tree expression (mandatory)
- sort array
- script fields with expressions
- highlight parameters
- limit of result set
- offset of result set
- `_source` - list of fields that will appear in the result set
- `profile` - when enabled, it returns profiling of the search query

A simple search example:
```php
$params = [
    'body' => [
        'table' => 'movies',
        'query' => [
            'match_phrase' => [
                'movie_title' => 'star trek nemesis',
            ]
        ],
        'sort' => ['_score','id'],
        'script_fields' =>['myexpr'=>['script'=>['inline'=>'IF(price<10,1,0)']]],
        'highlight' => ['fields'=>['title','content']],
        'limit' => 12,
        'offset' =>100,
        '_source'=>['title','content','cat_id'],
        'profile' => true
    ]
];

$response = $client->search($params);
```

The response will be a JSON object containing:

- `took` - query time
- `timed_out` - boolean, true if the query timed out
- `hits` - array with matches
- `profile` - optional, if profiling is set


### Insert

For a complete reference of payload and response, see Manticore's [Insert API](https://manual.manticoresearch.com/Data_creation_and_modification/Adding_documents_to_a_table/Adding_documents_to_a_real-time_table#Adding-documents-to-a-real-time-table).

`body` properties consist of:

- table name
- document as an array of properties
- id as a document id

All are mandatory.

```
$doc = [
    'body' => [
        'table' => 'testrt',
        'id' => 3,
        'doc' => [
            'gid' => 10,
            'title' => 'some title here',
            'content' => 'some content here',
            'newfield' => 'this is a new field',
            'unreal' => 'engine',
            'real' => 8.99,
            'j' => [
                'hello' => ['testing', 'json', 'here'],
                'numbers' => [1, 2, 3],
                'value' => 10.0
            ]
        ]
    ]
];

$response = $client->insert($doc);
```

If the table is the part of a cluster, the `body` must also contain the cluster name:
```
$doc = [
    'body' => [
        'table' => 'testrt',
        'cluster' => 'testcluster',
        'id' => 3,
        'doc' => [
            'gid' => 10,
            'title' => 'some title here',
            'content' => 'some content here',
            'newfield' => 'this is a new field',
            'unreal' => 'engine',
            'real' => 8.99,
            'j' => [
                'hello' => ['testing', 'json', 'here'],
                'numbers' => [1, 2, 3],
                'value' => 10.0
            ]
        ]
    ]
];

$response = $client->insert($doc);
```

### Replace

For a complete reference of payload and response, see Manticore's [Replace API](https://manual.manticoresearch.com/Data_creation_and_modification/Updating_documents/REPLACE).

`body` properties consist of:

- table name
- document as an array of properties
- id as a document id

All are mandatory.

```
$doc = [
    'body' => [
        'table' => 'testrt',
        'id' => 3,
        'doc' => [
            'gid' => 10,
            'content' => 'updated content here',
        ]
    ]
];

$response = $client->replace($doc);
```

If the table is the part of a cluster, the `body` must also contain the cluster name:

```
$doc = [
    'body' => [
        'table' => 'testrt',
        'cluster' => 'testcluster',
        'id' => 3,
        'doc' => [
            'gid' => 10,
            'content' => 'updated content here',
        ]
    ]
];

$response = $client->replace($doc);
```

### Update

For a complete reference of payload and response, see Manticore's [Update API](https://manual.manticoresearch.com/Data_creation_and_modification/Updating_documents/UPDATE).

`body` properties consist of:

- table name
- document as an array of properties
- id as a document id or a query array

If id is used, only one document can be updated:
```
$doc = [
    'body' => [
        'table' => 'testrt',
        'id' => 3,
        'doc' => [
            'gid' => 20,
        ]
    ]
];

$response = $client->update($doc);
```

With a query, it's possible to update multiple documents at a time:

```
$doc = [
    'body' => [
        'table' => 'testrt',
        'query' => ['match'=>['*' =>'find me']],
        'doc' => [
            'gid' => 20,
        ]
    ]
];

$response = $client->update($doc);
```

If the table is part of a cluster, the `body` must also contain the cluster name:
```
$doc = [
    'body' => [
        'table' => 'testrt',
        'cluster' => 'testcluster',
        'id' => 3,
        'doc' => [
            'gid' => 20,
        ]
    ]
];

$response = $client->update($doc);
```

### Delete

For a complete reference of payload and response, refer to Manticore's [Delete API](https://manual.manticoresearch.com/Data_creation_and_modification/Deleting_documents).

The `body` properties include:

- table name
- id as the document id

Both properties are required.

```
$doc = [
    'body' => [
        'table' => 'testrt',
        'id' => 3
    ]
];

$response = $client->delete($doc);
```

If the table is the part of a cluster, the `body` should also include the cluster name:

```
$doc = [
    'body' => [
        'table' => 'testrt',
        'cluster' => 'testcluster',
        'id' => 3
    ]
];

$response = $client->delete($doc);
```

### Bulk

For a complete reference of payload and response, refer to Manticore's [Bulk API](https://manual.manticoresearch.com/Data_creation_and_modification/Updating_documents/UPDATE#Bulk-updates).

Bulk enables sending multiple data manipulation operations (inserts, replaces, updates, or deletes) in a single request.

```php
$doc = [
    'body' => [
        ['insert' => [
            'table' => 'testrt',
            'id' => 34,
            'doc' => [
                'gid' => 1,
                'title' => 'a new added document',
            ]
        ]],
        ['update' => [
            'table' => 'testrt',
            'id' => 56,
            'doc' => [
                'gid' => 4,
            ]
        ]],
       [ 'delete' => [
            'table' => 'testrt',
            'id' => 100
        ]]
    ]
];

$response = $client->bulk($doc);
```

<!-- proofread -->