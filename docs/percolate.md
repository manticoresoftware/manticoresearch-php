# Working with Percolate indexes

## Percolate operations

For complete reference of payloads and responses see Manticore's [PQ API](https://docs.manticoresearch.com/latest/html/http_reference/json_pq.html)

Operations with the percolate indexes have their own namespace. The following methods are available:

## Inserting stored query

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


## Percolate search

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

## List stored queries

For [listing](https://docs.manticoresearch.com/latest/html/http_reference/json_pq.html#list-stored-queries) stored queries the `index` parameter is mandatory.

```
$params = [
    'index' => 'test_pq',
    'body' => [
    ]
];
$response = $client->pq()->search($params);
```

## Delete stored queries

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
