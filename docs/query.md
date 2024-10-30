# Query building classes

These can be used to build a query expression.

## BoolQuery()

Constructor of the bool node:
```php
$bool = new \Manticoresearch\Query\BoolQuery();
```

It supports adding nodes to it via `must()`, `mustNot()`, and `should()` methods.

Bool queries can be nested, meaning that nodes added to a root `BoolQuery` can, in turn, contain their own bool queries, allowing users to build complex logical expressions.

```php
$bool2 = new \Manticoresearch\Query\BoolQuery();
$bool2->should(new \Manticoresearch\Query\('_year', 2000));
$bool2->should(new \Manticoresearch\Query\('_year', 2010));
$bool->must($bool2);
```

## MatchQuery()

Creates a `match` leaf.
Constructor accepts:
- an array of keywords
- a string of fields delimited by a comma on which the keywords will be searched

 ```php
$bool->must(new \Manticoresearch\Query\MatchQuery(['query' => 'team of explorers', 'operator' => 'and'], 'title,content'));
```

## MatchPhrase()

Creates a `match` leaf.
Constructor accepts:
- a string containing a search phrase
- a string of fields delimited by a comma on which the keywords will be searched

 ```php
$bool->must(new \Manticoresearch\Query\MatchPhrase('team of explorers', 'title,content'));
```


## QueryString()

Creates a `query_string` leaf.
The constructor expects a string with a full-text match expression.


 ```php
$bool->must(new \Manticoresearch\Query\QueryString('"team of explorers"/2'));
```
## In()

Creates an `in` filter.

Expects two arguments: an attribute or alias name and an array with values.

 ```php
$bool->must(new \Manticoresearch\Query\In('_year', [2014,2015,2016]));
```

## Equals()

Creates an `equals` filter.

Expects two arguments: an attribute or alias name and a value.

 ```php
$bool->must(new \Manticoresearch\Query\Equals('_year', 2014));
```


## Range()

Creates an `equals` filter.

Expects two arguments: an attribute or alias name and an array of operator => value pairs.

 ```php
$bool->must(new \Manticoresearch\Query\Range('_year', ['lte' => 2020]));
```


## Distance()

Creates a `geo_distance` expression.
Expects an array that follows the syntax defined in `/search`:

- `location_anchor` containing the pin object
- `location_source` containing the attributes with lat/long
- `distance_type` -  can be `adaptive` (default) or `haversine`
- `distance` - a string with distance in format `XXX uom`, where `uom` can be meters, km, miles, yards, mm, feet, inches or nautical miles

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
$response = $search->search('"team of explorers"/2')->filter('_year', 'equals', 2014)->get();
```

can be rewritten as:
```php
$q = new \Manticoresearch\Query\BoolQuery();
$q->must(new \Manticoresearch\Query\MatchQuery(['query' => 'team of explorers', 'operator' => 'or'], '*'));
$q->must(new \Manticoresearch\Query\Equals('_year', 2014));
$response = $search->search($q)->get();
```

Both sugar syntaxes can also be mixed:

```php
$response = $search->search('"team of explorers"/2')->filter(new \Manticoresearch\Query\Equals('_year', 2014))->get();
```

## KnnQuery()

Constructor of the knn node:
```php
$knn1 = new \Manticoresearch\Query\KnnQuery('some_float_vector_field', [0.1, 0.45, 0.3], 5);
$knn2 = new \Manticoresearch\Query\KnnQuery('some_float_vector_field', 2, 5);
```
It accepts:
- a name of the `float_vector` type field
- a float vector or a document id to execute knn search by 
- a number of most similar documents to return

<!-- proofread -->