---
layout: default
title: Usage
nav_order: 3
has_children: true
---

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

A quick example:

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

### Client configuration


The client accepts a configuration as an array. Without any configuration provided, it tries to connect using HTTP `127.0.0.1` on port `9306`.

#### config paramaters


*  host  -  IP or DNS of server (part of a connection)
*  port -   HTTP API port (part of a connection)
*  connections - list of connections
*  connectionStrategy - name of connection pool strategy, default is `RoundRobin`
*  transport -  transport class name, default `Http` (part of a connection)

If there is a single connection used, it can be defined directly in the configuration array.
If multiple connections can used, they will be defined in `connections` array. 

#### Transport

Implemented transports:

* Http -  uses CURL extension
* Https  -  uses CURL extension, for https hosts
* PhpHttp - adapter for HTTPlug 1.0. A client and a message factory must be present in the environment.

Http/Https adapters options:

*  timeout -  connection timeout
*  connection_timeout - connection connect timeout
*  proxy  -  proxy definition as  host:port
*  username - username for HTTP Auth
*  password - password for HTTP Auth
*  headers - array of custom headers
*  curl - array of CURL settings as option=>value 
*  persistent -  define whenever connection is persistent or not, boolean value

Example:
```
        $params = ['connections'=>
            [
                [
                    'host' => '123.0.0.1',
                    'port' => '1234',
                    'timeout' => 5,
                    'connection_timeout' => 1,
                    'proxy' => '127.0.0.255',
                    'username' => 'test',
                    'password' => 'secret',
                    'headers' => [
                        'X-Forwarded-Host' => 'mydev.domain.com'
                    ],
                    'curl' => [
                        CURLOPT_FAILONERROR => true
                    ],
                    'persistent' => true
                ],
                [
                    'host' => '123.0.0.2',
                    'port' => '1235',
                    'timeout' => 5,
                    'transport' => 'Https',
                    'curl' =>[
                        CURLOPT_CAPATH => 'path/to/my/ca/folder',
                        CURLOPT_SSL_VERIFYPEER => true
                    ],
                    'connection_timeout' => 1,
                    'persistent' => true
                ],

            ]
        ];
        $client =  new Client($params);
```

#### Connection pool strategies


* Random - each query performed will use randomly one of the defined connections
* RoundRobin -  each query performed picks an alive connection in round robin fashion  
* StaticRoundRobin - connection are picked in round robin, but a connection is reused as long as it's alive. For example on first query connection 1 will be picked. If the connection works, query 2 will also use it and so on until the connection ends with hard error. In this case next queries will try to use the next connection from the pool (default)

On all strategies if a connection returns a hard error it will not be used in future attempts.

Custom strategy can be provided to the `connectionStrategy`. They must implement `Manticoresearch\Connection\Strategy\SelectorInterface`

```
$params = [
    'host' => '127.0.0.1',
    'post' => 9308,
    'connectionStrategy' => MyCustomStrategy::class
]
```
or 
```
$params = [
    'host' => '127.0.0.1',
    'post' => 9308,
    'connectionStrategy' => new MyCustomStrategy()
]
```

### Queries

All queries reflect the HTTP API making very easy to write them using the client.
Each method represents an API endpoint and accept an array with the following elements

* body -  the API endpoint POST payload
* index  - index name 
* id - document id
* query - endpoint parameters

Depending on the request, some of the parameters are mandatory.

On the body payload there is no check regarding the validity made before sending the request.

#### Search

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

#### SQL

```
$params = [
    'body' => [
        'query' => "SELECT * FROM movies where MATCH('@movie_title star trek')"
    ]
];

$response = $client->sql($params);
```

#### Percolate operations

##### Inserting stored query

`index` is required

Simple insertion:
```
$params = [
    'index' => 'pq',
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
    'index' => 'pq',
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

`index` is required

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


