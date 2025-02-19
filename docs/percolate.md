# Working with Percolate tables

## Percolate operations

For the complete reference of payloads and responses, see Manticore's [PQ API](https://manual.manticoresearch.com/Data_creation_and_modification/Adding_documents_to_a_table/Adding_rules_to_a_percolate_table).

Operations with percolate tables have their own namespace. The following methods are available:

## Inserting stored query

For [storing](https://manual.manticoresearch.com/Data_creation_and_modification/Adding_documents_to_a_table/Adding_rules_to_a_percolate_table) queries, the `table` parameter is mandatory.

`table` is mandatory.

Simple insertion with an auto-generated id:

```
$params = [
    'table' => 'test_pq',
    'body' => [
        'query' => ['match'=>['subject'=>'test']],
        'tags' => ['test1','test2']
    ]
];
$response = $client->pq()->doc($params);
```

Inserting with ID specified and refresh command:

```
$params = [
    'table' => 'test_pq',
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

For [searching](https://manual.manticoresearch.com/Searching/Percolate_query#Performing-a-percolate-query-with-CALL-PQ), the `table` parameter is required.

`table` is mandatory.

```
$params = [
    'table' => 'pq',
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

For [listing](https://manual.manticoresearch.com/Searching/Percolate_query#Performing-a-percolate-query-with-CALL-PQ) stored queries, the `table` parameter is required.

`table` is mandatory.

```
$params = [
    'table' => 'test_pq',
    'body' => [
    ]
];
$response = $client->pq()->search($params);
```

## Delete stored queries

For [deleting](https://manual.manticoresearch.com/Searching/Percolate_query#Performing-a-percolate-query-with-CALL-PQ) stored queries, the `table` parameter is required.

`table` is mandatory.

```
$params = [
    'table' => 'test_pq',
    'body' => [
        'id' => [5,6]
    ]
];
$response = $client->pq()->deleteByQuery($params);
```
<!-- proofread -->