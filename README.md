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
This client works only with Manticore Search 2.5.1 and above.

Install
--------

Use composer to add manticoresearch to your project:

```bash
composer require manticoresoftware/manticoresearch-php:dev-master
```

You can also directly add this line in the `require` block of `composer.json`:

```json
{
"require" : {
   "manticoresoftware/manticoresearch-php":"dev-master"
   }
}

```

and then use `composer install`.

Usage
----

Add

```php
require_once __DIR__ . '/vendor/autoload.php';
```

Then

```php
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
