manticoresearch-php
===================

Official client for Manticore Search using the HTTP JSON protocol. 


Features
--------
- One to one mapping with the HTTP JSON API
- connection pools with pluggable selection strategy. Defaults to static round robin
- pluggable PSR/Log interface
- pluggable transport protocols. Default to CURL (alternative HTTPLug 1.0)
- persistent connections

Compatibility
-------------
This  client works only with Manticore Search 2.5.1 and above.

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