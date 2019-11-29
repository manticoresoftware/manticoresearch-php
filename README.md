manticoresearch-php
===================

Official client for Manticore Search. 


For pre-release testing:
-----------------------

Create a compose.json file and add 
```
{
"require" : {
"manticoresoftware/manticoresearch-php":"dev-master"}
,"repositories":[
    {
        "type": "vcs",
        "url": "git@gitlab.com:manticoresearch/manticoresearch-php.git"
    }
]
}

``` 

then `composer update`

Usage
----

Add 
```
require_once __DIR__ . '/vendor/autoload.php';

```

Then
```
$config = ['host'=>'127.0.0.1','port'=>9308];
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