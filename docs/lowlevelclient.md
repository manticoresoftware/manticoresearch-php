Low-level client calls
======================

The [Search](searchclass.md) and [Index](indexclass.md) classes are build on top of low-level  Client class methods,
providing an easier way to construct the queries. 
Those familiar with the HTTP API syntax or wanting the ultimate speed can simply just use the Client class. 

The methods of Client class reflect as much as possible the same request payloads / responses of the Manticore Search HTTP API.

Beside operations on an index, there are also provided methods for performing tasks on the search server or search cluster.

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

    * [Indices](indices.md)
    * [Nodes](nodes.md)
    * [Cluster](cluster.md)
    
* [Running SQL](sql.md)


### Requests

Each request array can have one of the 

* body - it's content goes as the payload of the HTTP request 
* index/cluster  - index/cluster name
* id - document id
* query - endpoint URL parameters (not to be confused with `query` found in the payload of some responses)

Depending on the request, some of the parameters are mandatory.

On the body payload there is no check regarding the validity of it's structure before sending the request.


### Responses 

Responses are returned as arrays reflection of the response object received from the API endpoint. 


### Search
For complete reference of payload and response see Manticore's [Search API](https://docs.manticoresearch.com/latest/html/http_reference/json_search.html)

`body` properties:
- index name (mandatory)
- query  tree expression (mandatory)
- sort array
- script fields with expressions
- highlight parameters
- limit of result set
- offset of result set
- _source - list of fields that will appear in the result set
- profile - when enabled it returns profiling of the search query

A simple search example:
```php
$params = [
    'body' => [
        'index' => 'movies',
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

Response will be a JSON containing

- took - query time
- timed_out - boolean, true if query timed out
- hits - array with matches
- profile - optional, if profiling is set


### Insert

For complete reference of payload and response see Manticore's [Insert API](https://docs.manticoresearch.com/latest/html/http_reference/json_insert.html)

`body` properties consist of:

- index name
- document as array of properties
- id as document id

All are mandatory.

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

`body` properties consist of:

- index name
- document as array of properties
- id as document id

All are mandatory.

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

`body` properties consist of:

- index name
- document as array of properties
- id as document id or query array

If id is used, only one document can be updated:
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

With query it's possible to update multiple documents at a time:

```
$doc = [
    'body' => [
        'index' => 'testrt',
        'query' => ['match'=>['*' =>'find me']],
        'doc' => [
            'gid' => 20,
        ]
    ]
];

$response = $client->update($doc);
```


### Delete

For complete reference of payload and response see Manticore's [Delete API](https://docs.manticoresearch.com/latest/html/http_reference/json_delete.html)

`body` properties consist of:

- index name
- id as document id

All are mandatory.

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

```php
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
       [ 'delete' => [
            'index' => 'testrt',
            'id' => 100
        ]]
    ]
];

$response = $client->bulk($doc);
```

