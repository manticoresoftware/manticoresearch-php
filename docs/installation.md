
## Installation via Composer
We recommend to install the client using [Composer](http://getcomposer.org).

Use composer to add manticoresearch-php to your project:

```
  $ composer require manticoresoftware/manticoresearch-php
```


You can also directly add this line in the `require` block of `composer.json`:

```json
{
"require" : {
   "manticoresoftware/manticoresearch-php":"^0.1"
   }
}

```

and then use `composer install`.

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

