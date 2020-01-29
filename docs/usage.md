# Running Queries

A quick example of performing a simple search:

```
require_once __DIR__ . '/vendor/autoload.php';

$config = ['host'=>'127.0.0.1', 'port'=>9306];
$client = new \Manticoresearch\Client($config);

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


### Queries

All queries reflect the HTTP API making very easy to write them using the client.
Each method represents an API endpoint and accept an array with the following elements:

* body -  the API endpoint POST payload
* index  - index name 
* id - document id
* query - endpoint parameters

Depending on the request, some of the parameters are mandatory.

On the body payload there is no check regarding the validity made before sending the request.

### Responses 

Responses are returned as arrays reflection of the response object received from the API endpoint. 
There is no other change or parsing performed.

#### Search
For complete reference of payload and response see Manticore's [Search API](https://docs.manticoresearch.com/latest/html/http_reference/json_search.html)

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


#### Insert

For complete reference of payload and response see Manticore's [Insert API](https://docs.manticoresearch.com/latest/html/http_reference/json_insert.html)

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

#### Replace

For complete reference of payload and response see Manticore's [Replace API](https://docs.manticoresearch.com/latest/html/http_reference/json_replace.html)

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

#### Update

For complete reference of payload and response see Manticore's [Update API](https://docs.manticoresearch.com/latest/html/http_reference/json_update.html)

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

#### Delete

For complete reference of payload and response see Manticore's [Delete API](https://docs.manticoresearch.com/latest/html/http_reference/json_delete.html)

```
$doc = [
    'body' => [
        'index' => 'testrt',
        'id' => 3
    ]
];

$response = $client->delete($doc);
```

#### Bulk

For complete reference of payload and response see Manticore's [Bulk API](https://docs.manticoresearch.com/latest/html/http_reference/json_bulk.html)

Bulk allows to send in one request several operations of data manipulation (inserts,replaces, updates or deletes).

```
$doc = [
    'body' => [
        'insert' => [
            'index' => 'testrt',
            'id' => 34,
            'doc' => [
                'gid' => 1,
                'title' => 'a new added document',
            ]
        ],
        'update' => [
            'index' => 'testrt',
            'id' => 56,
            'doc' => [
                'gid' => 4,
            ]
        ],
        'delete' => [
            'index' => 'testrt',
            'id' => 100
        ]
    ]
];

$response = $client->bulk($doc);
```

#### SQL
For complete reference of payload and response see Manticore's [SQL API](https://docs.manticoresearch.com/latest/html/http_reference/sql.html).

```
$params = [
    'body' => [
        'query' => "SELECT * FROM movies where MATCH('@movie_title star trek')"
    ]
];

$response = $client->sql($params);
```

#### Percolate operations

For complete reference of payloads and responses see Manticore's [PQ API](https://docs.manticoresearch.com/latest/html/http_reference/json_pq.html)

Operations with the percolate indexes have their own namespace. The following methods are available:

##### Inserting stored query

For [storing](https://docs.manticoresearch.com/latest/html/http_reference/json_pq.html#store-query) queries the `index` parameter is mandatory. 

Simple insertion with auto generated id:

```
$params = [
    'index' => 'test_pq',
    'body' => [
        'query' => ['match'=>['subject'=>'test']],
        'tags' => ['test1','test2']
    ]
];
$response = $client->pq()->doc($params);
```

Inserting with id specified and refresh command:

```
$params = [
    'index' => 'test_pq',
    'id' =>101,
    'query' => ['refresh' =>1],
    'body' => [
        'query' => ['match'=>['subject'=>'testsasa']],
        'tags' => ['test1','test2']
    ]
];
$response = $client->pq()->doc($params);
```

##### Percolate search

For [searching](https://docs.manticoresearch.com/latest/html/http_reference/json_pq.html#search-matching-document) the `index` parameter is mandatory.

```
$params = [
    'index' => 'pq',
    'body' => [
        'query' => [
            'percolate' => [
                'document' => [
                    'subject'=>'test',
                    'content' => 'some content',
                    'catid' =>5
                ]
            ]
        ]
    ]
];
$response = $client->pq()->search($params);
```

##### List stored queries

For [listing](https://docs.manticoresearch.com/latest/html/http_reference/json_pq.html#list-stored-queries) stored queries the `index` parameter is mandatory.

```
$params = [
    'index' => 'test_pq',
    'body' => [
    ]
];
$response = $client->pq()->search($params);
```

##### Delete stored queries

For [deleting](https://docs.manticoresearch.com/latest/html/http_reference/json_pq.html#delete-stored-queries) stored queries the `index` parameter is mandatory.

```
$params = [
    'index' => 'test_pq',
    'body' => [
        'id' => [5,6]
    ]
];
$response = $client->pq()->deleteByQuery($params);
```
