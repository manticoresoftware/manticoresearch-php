<?php
require_once __DIR__ . '/vendor/autoload.php';
error_reporting(E_ALL);
$config = array(
    'connections' => [
        [
            'host' => '127.0.0.1',
            'port' => '6381'
        ],
        [
            'host' => '127.0.0.1',
            'port' => '6380'
        ],

    ],
    'connectionStrategy' => 'Random'
);
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

//$response = $client->search($params);
//print_r($response);

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

//$response = $client->insert($doc);

$params = [
    'index' => 'pq',
    'body' => [
        'query' => ['match'=>['subject'=>'test']],
        'tags' => ['test1','test2']
    ]
];
//$response = $client->pq()->doc($params);
$params = [
    'index' => 'pq',
    'id' => 102,
    'body' => [
        'query' => ['match'=>['subject'=>'findme']],
        'filters' => 'catid<10',
        'tags' => ['test1','test2']
    ]
];
//$response = $client->pq()->doc($params);
$params = [
    'index' => 'pq',
    'id' =>101,
    'query' => ['refresh' =>1],
    'body' => [
        'query' => ['match'=>['subject'=>'testsasa']],
        'tags' => ['test1','test2']
    ]
];
//$response = $client->pq()->doc($params);

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
print_r($response);