
## Installation via Composer
We recommend to install the client using [Composer](http://getcomposer.org).

Use composer to add manticoresearch-php to your project:

```
  $ composer require manticoresoftware/manticoresearch-php
```

Composer will download and take care of the autoloading of files.
To use the client you just have to include the autoload:


```
  require_once __DIR__ . '/vendor/autoload.php';

  $client = new \Manticoresearch\Client()
```

## Requirements


PHP 7.0+

Manticoresearch

Default driver uses CURL extension. An adapter for HTTPLug 1.0 is provided. 

