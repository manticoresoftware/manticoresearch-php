manticoresearch-php
===================

[![Build Status](https://travis-ci.org/manticoresoftware/manticoresearch-php.svg?branch=master)](https://travis-ci.org/manticoresoftware/manticoresearch-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/manticoresoftware/manticoresearch-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/manticoresoftware/manticoresearch-php/?branch=master)
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

#### Initiate the client:

```php
   require_once __DIR__ . '/vendor/autoload.php';
   ...
   $config = ['host'=>'127.0.0.1','port'=>9308];
   $client = new \Manticoresearch\Client($config);
```

#### Create index:
```php
    $index = [
        'index' => 'movies',
        'body' => [
            'columns' => ['title'=>['type'=>'text'],'plot'=>['type'=>'text'],'year'=>['type'=>'integer'],'rating'=>['type'=>'float']]
        ],
    ];
    $client->indices()->create($index);
````

#### Add a document:

```php
   $doc = [
        'index'=>'movies',
        'id' => 1,
        'doc' => [
            'title' => 'Star Trek: Nemesis',
            'plot' => 'The Enterprise is diverted to the Romulan homeworld Romulus, supposedly because they want to negotiate a peace treaty. Captain Picard and his crew discover a serious threat to the Federation once Praetor Shinzon plans to attack Earth.',
            'year' => 2002,
            'rating' => 6.4
        ]
    ];
    $client->insert(['body' =>$doc]);
```

#### Adding in bulk:

```php
    $docs =[
      ['insert'=> ['index'=>'movies','id'=>2,'doc'=>['title'=>'Interstellar','plot'=>'A team of explorers travel through a wormhole in space in an attempt to ensure humanity\'s survival.','year'=>2014,'rating'=>8.5]]],
      ['insert'=> ['index'=>'movies','id'=>3,'doc'=>['title'=>'Inception','plot'=>'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.','year'=>2010,'rating'=>8.8]]],
      ['insert'=> ['index'=>'movies','id'=>4,'doc'=>['title'=>'1917 ','plot'=>' As a regiment assembles to wage war deep in enemy territory, two soldiers are assigned to race against time and deliver a message that will stop 1,600 men from walking straight into a deadly trap.','year'=>2018,'rating'=>8.4]]],
      ['insert'=> ['index'=>'movies','id'=>5,'doc'=>['title'=>'Alien','plot'=>' After a space merchant vessel receives an unknown transmission as a distress call, one of the team\'s member is attacked by a mysterious life form and they soon realize that its life cycle has merely begun.','year'=>1979,'rating'=>8.4]]]
    ];
    $client->bulk(['body'=>$docs]);
```

#### Searching:

```php
   $search = [
        'body' => [
            'index' => 'movies',
            'query' => [
                        'match' => ['*' => 'space team'],
            ],
        ]
    ];
    $result = $client->search($search);
```
Response:

```json
{
    "took": 0,
    "timed_out": false,
    "hits": {
        "total": 2,
        "hits": [
            {
                "_id": "2",
                "_score": 1587,
                "_source": {
                    "year": 2014,
                    "rating": 8.5,
                    "title": "Interstellar",
                    "plot": "A team of explorers travel through a wormhole in space in an attempt to ensure humanity's survival."
                }
            },
            {
                "_id": "5",
                "_score": 1587,
                "_source": {
                    "year": 1979,
                    "rating": 8.4,
                    "title": "Alien",
                    "plot": " After a space merchant vessel receives an unknown transmission as a distress call, one of the team's member is attacked by a mysterious life form and they soon realize that its life cycle has merely begun."
                }
            }
        ]
    }
}
```
A search with matching and filters:

```php
   $search = [
        'body' => [
            'index' => 'movies',
            'query' => [
                'bool' => [
                    'must' =>[
                        'match' => ['*' => 'space team'],
                        'range' => ['year'=>['gte'=>2000],'rating'=>['gte'=>8.0]]
                    ]
                ]
            ],
            'sort' => ['_score',['year'=>'desc']]
        ]
    ];
    $result = $client->search($search);
```

#### Update a document:

```php
   $partial = [
        'body' => [
            'index' => 'movies',
            'id' => 4,
            'doc' => ['year'=>2019]
        ]
    ];
    $result = $client->update($partial);
```


#### Get index schema:

```php
    $client->indices()->describe(['index'=>'movies']);
```

#### Get list of all indices:

```php
    $client->nodes()->tables();
```


#### Delete index:

```php
    $client->indices()->drop(['index'=>'movies']);
```

#### Get server status:
```php
    $client->nodes()->status();
```



License
-------
Manticore Search PHP Client is an open-source software licensed under the [Apache v2.0 license](LICENSE.txt)


[slack-url]: https://slack.manticoresearch.com/
[slack-badge]:  https://img.shields.io/badge/Slack-join%20chat-green.svg