manticoresearch-php
===================
![Build Status](https://github.com/manticoresoftware/manticoresearch-php/actions/workflows/ci.yml/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/manticoresoftware/manticoresearch-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/manticoresoftware/manticoresearch-php/?branch=master)
[![codecov.io](https://codecov.io/github/manticoresoftware/manticoresearch-php/coverage.svg)](https://codecov.io/github/manticoresoftware/manticoresearch-php)
[![Latest Stable Version](https://poser.pugx.org/manticoresoftware/manticoresearch-php/v/stable)](https://packagist.org/packages/manticoresoftware/manticoresearch-php)
[![License](https://poser.pugx.org/manticoresoftware/manticoresearch-php/license)](https://packagist.org/packages/manticoresoftware/manticoresearch-php)
[![Slack][slack-badge]][slack-url]

[![Total Downloads](https://poser.pugx.org/manticoresoftware/manticoresearch-php/downloads)](https://packagist.org/packages/manticoresoftware/manticoresearch-php)
[![Monthly Downloads](https://poser.pugx.org/manticoresoftware/manticoresearch-php/d/monthly)](https://packagist.org/packages/manticoresoftware/manticoresearch-php)
[![Daily Downloads](https://poser.pugx.org/manticoresoftware/manticoresearch-php/d/daily)](https://packagist.org/packages/manticoresoftware/manticoresearch-php)
[![composer.lock](https://poser.pugx.org/manticoresoftware/manticoresearch-php/composerlock)](https://packagist.org/packages/manticoresoftware/manticoresearch-php)

[![GitHub Code Size](https://img.shields.io/github/languages/code-size/manticoresoftware/manticoresearch-php)](https://github.com/manticoresoftware/manticoresearch-php)
[![GitHub Repo Size](https://img.shields.io/github/repo-size/manticoresoftware/manticoresearch-php)](https://github.com/manticoresoftware/manticoresearch-php)
[![GitHub Last Commit](https://img.shields.io/github/last-commit/manticoresoftware/manticoresearch-php)](https://github.com/manticoresoftware/manticoresearch-php)
[![GitHub Activity](https://img.shields.io/github/commit-activity/m/manticoresoftware/manticoresearch-php)](https://github.com/manticoresoftware/manticoresearch-php)
[![GitHub Issues](https://img.shields.io/github/issues/manticoresoftware/manticoresearch-php)](https://github.com/manticoresoftware/manticoresearch-php/issues)


Official PHP client for Manticore Search.

❗ WARNING: this is a development version of the client. The latest release's readme is https://github.com/manticoresoftware/manticoresearch-php/tree/v2.3.1


Features
--------
- One to one mapping with the HTTP API
- connection pools with pluggable selection strategy. Defaults to static round robin
- pluggable PSR/Log interface
- pluggable transport protocols.
- persistent connections


Requirements
------------

Requires PHP 7.1 or greater with the native JSON extension. Default transport handler uses the cURL extension.

Minimum Manticore Search version is 2.5.1 with HTTP protocol enabled.

| Manticore Search  | manticoresearch-php |     PHP       |
| ----------------- | ------------------- | ------------- |
| >= 4.2.1          | 2.0.x, 2.1.x        | >= 7.1, >=8.0 |
| >= 4.0.2          | 1.7.x, 1.8.x        | >= 7.1, >=8.0 |
| >= 3.5.2          | 1.6.x               | >= 7.1, >=8.0 |
| >= 3.5.0          | 1.5.x               | >= 7.1, <8.0  |
| >= 3.5.0          | 1.4                 | >= 7.1, <8.0  |
| <= 3.4.x          | 1.3                 | >= 7.1, <8.0  |
| >= 2.5.1, <3.4.0  | 1.2                 | >= 7.1, <8.0  |

Documentation
-------------

Full documentation is available in  [docs](docs) folder.



Manticore Search server documentation: https://manual.manticoresearch.com/.


Getting Started
---------------

Install the Manticore Search PHP client using [composer](https://getcomposer.org) package manager:

```bash
composer require manticoresoftware/manticoresearch-php
```
### Initiate an index:

```php
require_once __DIR__ . '/vendor/autoload.php';

$config = ['host'=>'127.0.0.1','port'=>9308];
$client = new \Manticoresearch\Client($config);
$index = $client->index('movies');
```

### Create the index:

```php
$index->create([
    'title'=>['type'=>'text'],
    'plot'=>['type'=>'text'],
    'year'=>['type'=>'integer'],
    'rating'=>['type'=>'float']
    ]);
```

### Add a document:

```php
$index->addDocument([
        'title' => 'Star Trek: Nemesis',
        'plot' => 'The Enterprise is diverted to the Romulan homeworld Romulus, supposedly because they want to negotiate a peace treaty. Captain Picard and his crew discover a serious threat to the Federation once Praetor Shinzon plans to attack Earth.',
        'year' => 2002,
        'rating' => 6.4
        ],
    1);
```

### Add several documents at once:

```php
$index->addDocuments([
        ['id'=>2,'title'=>'Interstellar','plot'=>'A team of explorers travel through a wormhole in space in an attempt to ensure humanity\'s survival.','year'=>2014,'rating'=>8.5],
        ['id'=>3,'title'=>'Inception','plot'=>'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.','year'=>2010,'rating'=>8.8],
        ['id'=>4,'title'=>'1917 ','plot'=>' As a regiment assembles to wage war deep in enemy territory, two soldiers are assigned to race against time and deliver a message that will stop 1,600 men from walking straight into a deadly trap.','year'=>2018,'rating'=>8.4],
        ['id'=>5,'title'=>'Alien','plot'=>' After a space merchant vessel receives an unknown transmission as a distress call, one of the team\'s member is attacked by a mysterious life form and they soon realize that its life cycle has merely begun.','year'=>1979,'rating'=>8.4]
    ]); 
```

### Perform a search:

```php

$results = $index->search('space team')->get();

foreach($results as $doc) {
   echo 'Document:'.$doc->getId()."\n";
   foreach($doc->getData() as $field=>$value)
   {   
        echo $field.": ".$value."\n";
   }
}
```
Result:
```
Document:2
year: 2014
rating: 8.5
title: Interstellar
plot: A team of explorers travel through a wormhole in space in an attempt to ensure humanity's survival.

```
A text search with attribute filters:

```php

$results = $index->search('space team')
                 ->filter('year','gte',2000)
                 ->filter('rating','gte',8.0)
                 ->sort('year','desc')
                 ->get();

foreach($results as $doc) {
    echo 'Document:'.$doc->getId()."\n";
    foreach($doc->getData() as $field=>$value)
    {   
        echo $field.": ".$value."\n";
    }
}
```



### Update documents:

By document id:

```php
$index->updateDocument(['year'=>2019],4);

```

By query:

```php
$index->updateDocument(['year'=>2019],['match'=>['*'=>'team']]);

```


### Get index schema:
```php
$index->describe();
```

### Drop index:

```php
$index->drop();
```

The above will fail if the index does not exist.  To get around this pass a parameter of true, which cause the failure
to be silent.

```php
$index->drop(true);
```




License
-------
Manticore Search PHP Client is an open-source software licensed under the [MIT license](LICENSE.txt)


[slack-url]: https://slack.manticoresearch.com/
[slack-badge]:  https://img.shields.io/badge/Slack-join%20chat-green.svg
