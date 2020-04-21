# Query building classes

These can be used to build a query expression.

## BoolQuery()

Constructor of a the bool node
```php
$bool = new \Manticoresearch\Query\BoolQuery();
```

It supports adding nodes to it via `must()`, `mustNot()` and `should()` methods

## Match()

Creates a `match` leaf.
Constructor accepts:
- array of keywords
- string of fields delimited by comma on which the keywords will be searched

 ```php
$bool->must(new \Manticoresearch\Query\Match(['query' => 'team of explorers', 'operator' => 'and'], 'title,content'));
```

## MatchPhrase()

Creates a `match` leaf.
Constructor accepts:
- string with a search phrase
- string of fields delimited by comma on which the keywords will be searched

 ```php
$bool->must(new \Manticoresearch\Query\MatchPhrase('team of explorers', 'title,content'));
```


## QueryString()

Creates a `query_string` leaf.
Constructor expects a string with a full-text match expression.


 ```php
$bool->must(new \Manticoresearch\Query\QueryString('"team of explorers"/2'));
```

## Equals()

Creates a `equals` filter.

Expects two arguments: an attribute or alias name and a value

 ```php
$bool->must(new \Manticoresearch\Query\Equals('year', 2014));
```


## Range()

Creates a `equals` filter.

Expects two arguments: an attribute or alias name and an array of operator => value pairs.

 ```php
$bool->must(new \Manticoresearch\Query\Range('year', ['lte' => 2020]));
```


## ScriptFields()

Creates a `script_fields` expression.

Expects two arguments:
- expression name
- a string with an expression in SQL syntax

 ```php
$bool->must(new \Manticoresearch\Query\ScriptFields('cond1', "IF (IN (content_rating,'TV-PG','PG'),2, IF(IN(content_rating,'TV-14','PG-13'),1,0))"));
```

The expression name can be used in filtering or sorting.

## Distance()

Creates a `geo_distance` expression.
Expects an array that follows the syntax defined in `/json/search` :

- location_anchor containing the pin object
- location_source containing the attributes with lat/long
- distance_type -  can be 'adaptive' (default) or 'haversine'
- distance - a string with distance in format `XXX uom` , where `uom` can be meters, km, miles, yards, mm, feed, inches or nautical miles

The pin location and the lat/long attributes must be in degrees.

```php
$bool->must(new \Manticoresearch\Query\Distance([
                     'location_anchor'=>
                         ['lat'=>52.396, 'lon'=> -1.774],
                     'location_source' => 
                        ['latitude_deg', 'longitude_deg'],
                     'location_distance' => '10000 m'
                 ]));
```


## Examples

This code :
```php
$response = $search->search('"team of explorers"/2')->filter('year', 'equals', 2014)->get();
```

can be rewritten as:
```php
$q = new BoolQuery();
$q->must(new Match(['query' => 'team of explorers', 'operator' => 'or'], '*'));
$q->must(new Equals('year', 2014));
$response = $search->search($q)->get();
```

Both sugar syntaxes can be also be mixed:

```php
$response = $search->search('"team of explorers"/2')->filter(new Equals('year', 2014))->get();
```
