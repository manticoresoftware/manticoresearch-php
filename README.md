manticoresearch-php
===================

[![Build Status](https://travis-ci.org/manticoresoftware/manticoresearch-php.svg?branch=master)](https://travis-ci.org/manticoresoftware/manticoresearch-php)
[![Latest Stable Version](https://poser.pugx.org/manticoresoftware/manticoresearch-php/v/stable)](https://packagist.org/packages/manticoresoftware/manticoresearch-php)
[![License](https://poser.pugx.org/manticoresoftware/manticoresearch-php/license)](https://packagist.org/packages/manticoresoftware/manticoresearch-php)
[![Slack][slack-badge]][slack-url]

Official PHP client for Manticore Search.


Features
--------
- One to one mapping with the HTTP  API
- connection pools with pluggable selection strategy. Defaults to static round robin
- pluggable PSR/Log interface
- pluggable transport protocols.
- persistent connections


Requirements
------------

Requires PHP 7.0 or greater with the native JSON extension. Default transport handler uses the cURL extension.

Minimum Manticore Search version is 2.5.1 with HTTP protocol enabled.
Some commands which are not yet implemented natively in the HTTP protocol are emulated via `/sql` and require Manticore Search 3.4. 

Documentation
-------------

Full documentation is available in  [docs](docs) folder.

Manticore Search server documentation: https://docs.manticoresearch.com/latest/html/.


Getting Started
---------------

Install the Manticore Search PHP client using [composer](https://getcomposer.org) package manager: 

```bash
composer require manticoresoftware/manticoresearch-php
```

Once installed, you can start using the client to perform search queries:

```
   require_once __DIR__ . '/vendor/autoload.php';
   ...
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


License
-------
Manticore Search PHP Client is an open-source software licensed under the [Apache v2.0 license](LICENSE.txt)


[slack-url]: https://slack.manticoresearch.com/
[slack-badge]:  https://img.shields.io/badge/Slack-join%20chat-green.svg