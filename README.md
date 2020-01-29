manticoresearch-php
===================

Official client for Manticore Search using the HTTP JSON protocol.

| WARNING: This software is currently under development and not ready for production. |
| --- |


Features
--------
- One to one mapping with the HTTP JSON API
- connection pools with pluggable selection strategy. Defaults to static round robin
- pluggable PSR/Log interface
- pluggable transport protocols. Default to CURL (alternative HTTPLug 1.0)
- persistent connections

Requirements
------------

This client works only with Manticore Search 2.5.1 and above.

Requires PHP 7.0 or greater with the native JSON extension. It's recommended to have Curl enabled in your PHP setup.

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

Documentation
-------------

For manual see  [docs](docs) folder.

For complete API reference of the client check https://manticoresoftware.github.io/manticoresearch-php/api

Manticore Search server documentation: https://docs.manticoresearch.com/latest/html/


License
-------
Manticore Search PHP Client is an open-source software licensed under the [Apache v2.0 license](LICENSE.txt)