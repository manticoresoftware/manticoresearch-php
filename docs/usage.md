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


### List of APIs


[Search API](search.md)

[Data APIs](data.md)

[SQL API](sql.md)

[Percolate API](percolate.md)

Namespaces:

[Indices](indices.md)
[Nodes](nodes.md)
[Cluster](cluster.md)



