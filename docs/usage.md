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

* [Percolate searches](percolate.md)

* [Keyword helpers](keywordhelpers.md)

* Administrative operations

    * [Indices](indices.md)
    * [Nodes](nodes.md)
    * [Cluster](cluster.md)
    
* [Running SQL](sql.md)

* [Error handling](errors.md)

### Requests

Each request array can have one of the 

* body -  the API endpoint POST payload
* index/cluster  - index/cluster name
* id - document id
* query - endpoint parameters

Depending on the request, some of the parameters are mandatory.

On the body payload there is no check regarding the validity made before sending the request.

### Responses 

Responses are returned as arrays reflection of the response object received from the API endpoint. 


### Search
For complete reference of payload and response see Manticore's [Search API](https://docs.manticoresearch.com/latest/html/http_reference/json_search.html)

`body` requires presence of `index` and `query` parameters. 

A simple search example:
```
$params = [
    'body' => [
        'index' => 'movies',
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

For complete reference of payload and response see Manticore's [Insert API](https://docs.manticoresearch.com/latest/html/http_reference/json_insert.html)

`body` requires presence of `index`, `id` and  `doc` parameters.

```
$doc = [
    'body' => [
        'index' => 'testrt',
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

For complete reference of payload and response see Manticore's [Replace API](https://docs.manticoresearch.com/latest/html/http_reference/json_replace.html)

`body` requires presence of `index`, `id` and  `doc` parameters.

```
$doc = [
    'body' => [
        'index' => 'testrt',
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

For complete reference of payload and response see Manticore's [Update API](https://docs.manticoresearch.com/latest/html/http_reference/json_update.html)

`body` requires presence of `index`, `id` and  `doc` parameters.

```
$doc = [
    'body' => [
        'index' => 'testrt',
        'id' => 3,
        'doc' => [
            'gid' => 20,
        ]
    ]
];

$response = $client->update($doc);
```

### Delete

For complete reference of payload and response see Manticore's [Delete API](https://docs.manticoresearch.com/latest/html/http_reference/json_delete.html)

`body` requires presence of `index` and `id`  parameters.

```
$doc = [
    'body' => [
        'index' => 'testrt',
        'id' => 3
    ]
];

$response = $client->delete($doc);
```

### Bulk

For complete reference of payload and response see Manticore's [Bulk API](https://docs.manticoresearch.com/latest/html/http_reference/json_bulk.html)

Bulk allows to send in one request several operations of data manipulation (inserts,replaces, updates or deletes).

```
$doc = [
    'body' => [
        ['insert' => [
            'index' => 'testrt',
            'id' => 34,
            'doc' => [
                'gid' => 1,
                'title' => 'a new added document',
            ]
        ]],
        ['update' => [
            'index' => 'testrt',
            'id' => 56,
            'doc' => [
                'gid' => 4,
            ]
        ]],
        ['delete' => [
            'index' => 'testrt',
            'id' => 100
        ]]
    ]
];

$response = $client->bulk($doc);
```
