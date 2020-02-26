
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