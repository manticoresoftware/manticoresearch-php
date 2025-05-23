Running Queries
===============

Table of Contents
-----------------

* [General notes on requests](#requests) 

* [Search](#search)

* [Insert documents](#insert)

* [Update documents](#update)

* [Replace documents](#replace)

* [Delete documents](#delete)

* [Bulk operations with documents](#bulk)

* [Autocomplete](#autocomplete)

* [Percolate searches](percolate.md)

* [Keyword helpers](queryhelpers.md)

* Administrative operations

    * [Tables](tables.md)
    * [Nodes](nodes.md)
    * [Cluster](cluster.md)
    
* [Running SQL](sql.md)

* [Error handling](errors.md)

### Requests

Each request array can include one of the following:

* body - the API endpoint POST payload
* table/cluster - table/cluster name
* id - document id
* query - endpoint parameters

Some parameters are mandatory, depending on the request.

No validity check is performed on the body payload before sending the request.

### Responses

Responses are returned as arrays reflecting the response object received from the API endpoint.


### Search
For a complete reference of payload and response, see Manticore's [Search API](https://manual.manticoresearch.com/Searching/Full_text_matching/Basic_usage#HTTP-JSON).

`body` requires the presence of the `table` and `query` parameters.

A simple search example:
```
$params = [
    'body' => [
        'table' => 'movies',
        'query' => [
            'match_phrase' => [
                'movie_title' => 'star trek nemesis',
            ]
        ]
    ]
];

$response = $client->search($params);
```

### Insert

For a complete reference of payload and response, see Manticore's [Insert API](https://manual.manticoresearch.com/Data_creation_and_modification/Adding_documents_to_a_table/Adding_documents_to_a_real-time_table#Adding-documents-to-a-real-time-table).

`body` requires the presence of the `table`, `id`, and `doc` parameters.

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

### Replace

For a complete reference of payload and response, see Manticore's [Replace API](https://manual.manticoresearch.com/Data_creation_and_modification/Updating_documents/REPLACE).

`body` requires the presence of the `table`, `id`, and `doc` parameters.

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

### Update

For a complete reference of payload and response, see Manticore's [Update API](https://manual.manticoresearch.com/Data_creation_and_modification/Updating_documents/UPDATE).

`body` requires the presence of the `table`, `id`, and `doc` parameters.

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

### Delete

For a complete reference of payload and response, see Manticore's [Delete API](https://manual.manticoresearch.com/Data_creation_and_modification/Deleting_documents).

`body` requires the presence of both `table` and `id` parameters.

```
$doc = [
    'body' => [
        'table' => 'testrt',
        'id' => 3
    ]
];

$response = $client->delete($doc);
```

### Bulk

For a complete reference of payload and response, see Manticore's [Bulk API](https://manual.manticoresearch.com/Data_creation_and_modification/Updating_documents/UPDATE#Bulk-updates)

Bulk allows sending multiple data manipulation operations (inserts, replaces, updates, or deletes) in one request.

```
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
        ['delete' => [
            'table' => 'testrt',
            'id' => 100
        ]]
    ]
];

$response = $client->bulk($doc);
```

### Autocomplete

For a complete reference of payload and response, see Manticore's [Autocomplete manual](https://manual.manticoresearch.com).

The request requires `query` and `table` defined as required parameters.

```
$request = [
    'body' => [
        'query' => 'hello wo',
        'table' => 'testrt',
    ],
];

$response = $client->autocomplete($request);
```

<!-- proofread -->
