# Search

This class allows you to perform search operations.


## Initiation


```php
require_once __DIR__ . '/vendor/autoload.php';
use Manticoresearch\Client;
use Manticoresearch\Search;
$config = ['host' => '127.0.0.1', 'port' => '9308'];
$client = new Client($config);
$search = new Search($client);
```

## Set the table
```php
$search->setTable('tablename');
```


## Performing a search

All methods of the `Search` class can be chained.

When all the search conditions and options are set, `get()` is called to process and query the search engine.

The `get()` method returns the results as a [ResultSet](searchresults.md#resultset-object) object.

### search()

This method accepts either a full-text match string or a [BoolQuery](query.md#boolquery) object.

The full Manticore query syntax (https://manual.manticoresearch.com/Searching/Full_text_matching/Operators) is supported.

```php
$search->search('find me')->get();
```

It returns a [ResultSet](searchresults.md#resultset-object) object.

Note that the query string must be escaped as described [here](https://manual.manticoresearch.com/Searching/Full_text_matching/Escaping#Escaping-characters-in-query-string)

### match()

`Match` is a simplified search method. The query string is interpreted as a bag of words with OR as the default operator.

The first parameter can be a query string:

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

Set limit and offset for the result set:

```php
$search->limit(24)->offset(12);
```

### maxMatches()

Set [max_matches](https://manual.manticoresearch.com/Searching/Options#max_matches) for the search.

```php
$search->limit(10000)->maxMatches(10000);
```

### join()

Performs a query with [joined tables](https://manual.manticoresearch.com/dev/Searching/Joining#Joining-tables) 

```php
$joinQuery = new JoinQuery('inner', 'joined_table_name', 'main_table_field_name', 'joined_table_field_name');
$search->join($joinQuery, true);
```

It expects 2 parameters:
- a `JoinQuery` object
- a boolean flag to remove previously added join queries before adding a new one. Optional, set to `false` by default. 


### knn()

Performs a [KNN search](https://manual.manticoresearch.com/Searching/KNN).

```php
$search->knn('some_float_vector_field', [0.567, 0.322], 100);
```

The target can be a vector, document ID, or text query:

```php
$search->knn('some_float_vector_field', 5, 100);
$search->knn('some_float_vector_field', 'some_query', 100);
```

The optional fourth argument adds properties to the KNN object, such as a name used by weighted RRF fusion:

```php
$search->knn(
    'some_float_vector_field',
    [0.567, 0.322],
    100,
    ['name' => 'dense']
);
```

Multiple KNN searches can be passed as associative array:

```php
$search
    ->search('machine learning')
    ->knn([
        [
            'field' => 'title_vector',
            'target' => [0.1, 0.2],
            'k' => 100,
            'options' => ['name' => 'title_dense'],
        ],
        [
            'field' => 'description_vector',
            'target' => [0.3, 0.4],
            'k' => 100,
            'options' => ['name' => 'description_dense'],
        ],
    ])
    ->rrf();
```

Each definition requires `field`, `target`, and `k`; `options` is optional. Calling `knn()` with multiple KNN items without `rrf()` raises an exception.

Text targets require an auto-embedding float-vector field, as described in [Creating a table with auto-embeddings](https://manual.manticoresearch.com/Searching/KNN#Creating-a-table-with-auto-embeddings).

### rrf()

Enables [Reciprocal Rank Fusion](https://manual.manticoresearch.com/Searching/Hybrid_search) for a full-text query combined with one or more KNN queries:

```php
$search
    ->search('machine learning')
    ->knn('embedding', [0.1, 0.2], 100)
    ->rrf([
        'rank_constant' => 10,
        'window_size' => 200,
    ]);
```

The options array can contain `rank_constant`, `window_size`, and `fusion_weights`. Named KNN entries are required when assigning KNN weights:

```php
$search
    ->search('machine learning')
    ->knn('embedding', [0.1, 0.2], 100, ['name' => 'dense'])
    ->rrf(['fusion_weights' => ['query' => 0.7, 'dense' => 0.3]]);
```

### hybrid()

Provides Manticore's simplified hybrid-search syntax for tables with auto-embeddings:

```php
$search->hybrid('machine learning');
$search->hybrid('machine learning', 'embedding');
```

The vector field is optional when Manticore can detect a single auto-embedding field. The `hybrid` shorthand cannot be combined with `knn()`.

### expression() and expressions()

`expression()` adds a computed field to the request:

```php
$search->expression('discounted_price', 'price * 0.9');
```

`expressions()` sets the complete shorthand expression map:

```php
$search->expressions([
    'discounted_price' => 'price * 0.9',
    'title_hash' => 'crc32(title)',
]);
```

Note that expression names must be lowercase. 

### filter(), orFilter() and notFilter()

Allow adding an attribute filter.

It can expect 3 parameters for filtering an attribute:

- attribute name. It can also be an alias of an expression;
- operator. Accepted operators are `range`, `lt`, `lte`, `gt`,  `gte`, `equals`, `in`;
- values for filtering. It can be an array or a single value. Currently, filters support integer, float, and string values.
- filtering condition. It can accept one of `AND`, `OR`, `NOT` values. Set to `AND` by default.

`notFilter()` executes a negation of the operator. Alternatively, the `NOT` filtering condition can be used.

`orFilter()` executes logical disjunction in case of multiple filters. Alternatively, the `OR` filtering condition can be used.

```php
$search->filter('_year', 'equals', 2000);
$search->filter('_year', 'lte', 2000);
$search->filter('_year', 'range', [1960,1992]);
$search->filter('_year', 'in', [1960,1975,1990]);
```

```php
$search->filter('_year', 'equals', 2000, 'OR');
$search->filter('_year', 'equals', 2002, 'OR');

$search->filter('_year', 'range', [1960,1992], 'OR');
$search->filter('_year', 'range', [1995,2000], 'OR');

$search->orFilter('_year', 'equals', 2000);
$search->orFilter('_year', 'equals', 2002);

$search->orFilter('_year', 'range', [1960,1992]);
$search->orFilter('_year', 'range', [1995,2000]);
```

```php
$search->filter('_year', 'equals', 2000, 'NOT');
$search->filter('_year', 'lte', 1995, 'NOT');

$search->notFilter('_year', 'equals', 2000);
$search->notFilter('_year', 'lte', 1995);

```


Note that the `equals` operator can be omitted, and the filter function can be called only with the `value` parameter, as shown in the example below:

```php
$search->filter('_year', 2000);
```

The functions can also accept a single parameter as a filter class like `Range()`, `Equals()`, or `Distance()`.

```php
$search->filter(new Range('_year', ['lte' => 2020]));
```

### sort()

Adds a sorting rule. The sorting rules will be applied in the order they are added.

It can accept two parameters:

- attribute or alias name
- direction of sorting; can be `asc` or `desc`

If the attribute is a MVA (multi-valued attribute), a third parameter can be used to set which value to choose from the list:
- mode can be `min` or `max`

```php
$search->sort('name','asc');
$search->sort('tags','asc','max');
```

`Sort` can also accept the first argument as an array with key-value pairs as attribute -> direction:

```php
$search->sort(['name'=>'asc', 'tags'=>'asc']);
````

By default, rules are added to the existing ones. If the second argument is set, the input array will not be added but will replace an existing rule set.

```php
$search->sort(['name'=>'asc', 'tags'=>'asc'], true);
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

The `sort` method can be chained. For example:

```php
$search->sort('name','asc')->sort('tags', 'desc')->sort('_year', 'asc');
```

Note that the maximum number of attributes to sort by is equal to 5.

### highlight()

Enables highlighting.

The function can accept two parameters, and neither is mandatory.

- `fields` - an array with field names from which to extract the texts for highlighting. If missing, all `text` fields will be used.
- `settings` - an array with settings for highlighting. For more details, check the HTTP API [Text highlighting](https://manual.manticoresearch.com/Searching/Full_text_matching/Basic_usage#HTTP-JSON).

```php
$search->highlight();
```

```php
$search->highlight(
    ['title'],
    ['pre_tags' => '<i>','post_tags'=>'</i>']
);
```

The highlight excerpts are attached to each hit. They can be retrieved with the `getHighlight()` function of the [ResultHit](searchresults.md#resulthit-object).

`getHighlight()` will contain a list of excerpts for each field declared for highlighting in the request.

### setSource()

By default, all document fields are returned. This method can set which fields should be returned. It accepts several formats:

- setSource('attr*') - only fields like `attr*` will be returned.
- setSource(['attr1','attr2']) - only fields `attr1` and `attr2` will be returned.
- setSource([
    'included' => ['attr1','attri*'],
    'excludes' => ['desc*']
  ]) - field `attr1` and fields like `attri*` are included, while any field like `desc*` is excluded. If an attribute is found in both lists, the excluding wins.


### aggregation() and aggregations()

`aggregation()` adds or replaces one named aggregation:

```php
$search->aggregation('average_price', 'avg', ['field' => 'price']);
```

It supports any aggregation type accepted by Manticore, including `date_histogram`, `range`, `date_range`, `percentiles`, `percentile_ranks`, `median_absolute_deviation`, `min`, `max`, `sum`, and `avg`.

`aggregations()` replaces the complete raw `aggs` tree:

```php
$search->aggregations([
    'price_ranges' => [
        'range' => [
            'field' => 'price',
            'ranges' => [
                ['to' => 100],
                ['from' => 100, 'to' => 500],
                ['from' => 500],
            ],
        ],
    ],
]);
```

Pass `null` to `aggregations()` to remove all aggregations. Results are available through [ResultSet:getAggregations()](searchresults.md#resultset-object). The existing `facet()` and `multiFacet()` methods remain convenience builders for `terms` and `composite` aggregations.

### facetFilterMode()

Controls how all facets inherit filters from the main query. Supported modes are available as constants:

```php
$search->facetFilterMode(Search::FACET_FILTER_MODE_STRICT);
$search->facetFilterMode(Search::FACET_FILTER_MODE_AUTO);
$search->facetFilterMode(Search::FACET_FILTER_MODE_MAX);
```

- `strict` applies every main-query filter.
- `auto` excludes filters on the facet's own field and adds bucket statuses.
- `max` uses the broad base-query bucket set and adds bucket statuses.

See [Facet filter modes](https://manual.manticoresearch.com/Searching/Faceted_search#Facet-filter-modes) for behavior and performance considerations.

### facet()
The `facet()` method allows you to add a facet (aggregation) to your search query.

```php
$search->facet($field, $group = null, $limit = null, $sortField = null, $sortDirection = 'desc');
```
Parameters:
 * field - name of the attribute to group by. This is a required parameter and can also be an expression name.
 * group - Facet name. If not provided, the attribute name will be utilized.
 * limit - Defines the maximum number of facet values to return.
 * sortField - Field the facet values will be sorted by. Also, can be set as `COUNT(*)` or `FACET()`. For details, see [Ordering in facet result](https://manual.manticoresearch.com/Searching/Faceted_search#Ordering-in-facet-result).
 * sortDirection - Direction of sorting, `desc` by default

Facets will be included in the result set and can be accessed using [ResultSet:getFacets()](searchresults.md#resultset-object).

### multiFacet()

The `multiFacet()` method allows you to add a facet (aggregation) composed by multiple fields to your search query.

```php
$search->multiFacet($group = null, $limit = null);
```
Parameters:
 * group - Facet name. This is a required parameter.
 * limit - Defines the maximum number of facet values to return.

Multi field facets will be included in the result set and can be accessed using [ResultSet:getFacets()](searchresults.md#resultset-object).


### option()

Pass options to the search query.
You can also customize the ranker by setting a custom expression. Check out the available [built-in rankers](https://manual.manticoresearch.com/Searching/Sorting_and_ranking#Formula-expressions-for-all-the-built-in-rankers).

```php
    $search->option('cutoff', 1);
    $search->option('retry_count', 3);
    $search->option('field_weights', ['title' => 100, 'description' => 200]);

    // chain options
    $search->option('ranker', 'sph04')->option('retry_delay', 5);
    //set a custom ranker based on field values
    $search->option('ranker', 'expr(\'sum((4*lcs+2*(min_hit_pos==1)+exact_hit)*user_weight)*1000 + bm25 + IF(IN(field1, "1"), 0, 10000) + IF(IN(field2, "1"), 0, 10000)\')')
    
    // unset options by passing null
    $search->option('ranker', null);
```

### trackScores()
Enables weight calculation by setting the `track_scores` option to `true`.

```php
    $search->trackScores(true); // enables weight calculation
    $search->trackScores(false); // disables weight calculation
    $search->trackScores(null); // unsets track_scores option
```

### stripBadUtf8()
Enables the removal of bad UTF-8 characters from the results by setting the `strip_bad_utf8` option to `true`.

```php
    $search->stripBadUtf8(true); // enables the removal of bad utf8 characters
    $search->stripBadUtf8(false); // disables the removal of bad utf8 characters
    $search->stripBadUtf8(null); // unsets the strip_bad_utf8 option
```

### profile()

Provides query profiling in the result set.


### reset()

This method clears all search conditions, including the table name.

<!-- proofread -->
