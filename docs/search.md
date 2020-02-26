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
