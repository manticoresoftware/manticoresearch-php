# Search

This class allow performing search operations.


## Initiation


```php
require_once __DIR__ . '/vendor/autoload.php';
use Manticoresearch\Client;
use Manticoresearch\Search;
$config = ['host' => '127.0.0.1', 'port' => '9308'];
$client = new Client($config);
$search = new Search($client);
```

## Set the index
```php
$search->setIndex('indexname');
```


## Performing a search

All methods of Search class can be chained. 

When all the search conditions and options are set, `get()` will be called to process and query the search engine.

The get() method will return the results as a [ResultSet](searchresults.md#resultset-object) object.

### search()

It can accept a string a full-text match string or a [BoolQuery](query.md#boolquery) object. 

The full Manticore query syntax (https://docs.manticoresearch.com/latest/html/searching/extended_query_syntax.html) is supported.

```php
$search->search('find me')->get();
```

It returns a [ResultSet](searchresults.md#resultset-object)  object.

### match()

Match is a simplified search method. The query string is interpreted as bag of words with OR as default operator.

The first parameter can be a query string

```php
$search->match('find me fast');
```
or an array containing the query string and the operator:
```php
$search->match(['query'=>'find me fast','operator'=>'and']);
```
If the match should be restricted to one or more `text` fields, they can be set in a second argument:

```php
$search->match('find me fast','title,long_title');
```

### limit() and offset()

Set limit and offset for the result set

```php
$search->limit(24)->offset(12);
```

### filter() and notFilter()

Allow adding an attribute filter.

It can expect 3 parameters for filtering an attribute:

- attribute name. It can also be an alias of an expression;
- operator. Accepted operators are `range`, `lt`, `lte`, `gt`,  `gte`, `equals`;
- values for filtering. It can an array or single value

notFilter() executes a negation of the operator.

```php
$search->filter('year', 'lte', 2000);
$search->filter('year', 'range', [1960,1992]);
```

The functions can also accept a single parameter as a filter class like Range(),  Equals() or Distance()

```php
$search->filter(new Range('year', ['lte' => 2020]));
```


### sort()

Adds a sorting rule. The sort rules will be used as they are added.

It can accept two parameters:

- attribute or alias name
- direction of sorting; can be `asc' or `desc`

If the attribute is a MVA, a third parameter can be used to set which value to choose from the list
- mode can be `min` or `max`

```php
$search->sort('name','asc');
$search->sort('tags','asc','max');
```

Sort can also accept the first argument as an array with key-pairs as attribute -> direction:

```php
$search->sort(['name'=>'asc']);
````
By default, rules are added. If second argument is set, the input array will not be added, but replace an existing rule set

```php
$search->sort(['name'=>'desc'],true);
````

The first argument can also be a geo distance sort expression:

```php
$search->sort([
               '_geo_distance' =>[
                   'location_anchor'=>
                       [
                           'lat'=>52.396,
                           'lon'=> -1.774
                       ],
                   'location_source' => [
                       'latitude_deg',
                       'longitude_deg'
                   ]
               ]
           ]);
````
### highlight()

Enables highlighting.

The function can accept two parameters and none is mandatory.

- fields - array with field names from which to extract the texts for highlighting. If missing, all `text` fields will be used
- settings - array with settings of highlighting. For more details check HTTP API [Text highlighting](https://docs.manticoresearch.com/latest/html/http_reference/json_search.html#text-highlighting) 

```php
$search->highlight();
```

```php
$search->highlight(
    ['title'],
    ['pre_tags' => '<i>','post_tags'=>'</i>']
);
```

The highlight excerpts are attached to each hit. They can be retrieved with `getHighlight()` function of [ResultHit](searchresults.md#resulthit-object).

`getHighlight()`  will contain a list of excerpts  for each field declared for highlighing in the request.

### setSource()

By default all document fields are returned. This method can set which fields should be returned. It accepts several formats:

- setSource('attr*') -  only fields like `attr*` will be returned
- setSource(['attr1','attr2']) - only fields `attr1` and `attr2` will be returned
- setSource([
    'included' => ['attr1','attri*'],
    'excludes' => ['desc*']
  ]) -  field `attr1` and fields like `attri*` are included, any field like `desc*` are excluded. If an attribute is found in both lists, the excluding wins

### profile()

If included, result set will provide query profiling.


### reset()

It clears all search conditions, including the index name.
