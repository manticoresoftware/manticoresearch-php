# Search result objects

The native [Client:search](lowlevelclient.md#search) method returns an unmodified array by default, which represents the response of the `/search` endpoint, such as:

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


## ResultSet object

[Search:search](searchclass.md#search) returns a `ResultSet` object, which implements both the `Iterator` and `Countable` interfaces.

```php
$result = $search->search('...')->get();
echo  count($result);
echo $result->count();
foreach($result as $hit)
{
   // do something with $hit
}
```   
To obtain the count of documents in the response, you can use either the `count()` function or the object's `count()` method.

Iterating through the result set object provides a `ResultHit` object containing the matched document.

The `ResultSet` object also offers information about the query:

Total search matches:

```php
$result->getTotal();
```

Scroll token if one was passed:

```php
$result->getScroll();
```


Query time:
```php
$result->getTime();
```

Check if the query timed out:

```php
$result->hasTimedout();
```

Facets (aggregations):

```php
$facets = $result->getFacets();
// Alias
$facets = $result->getAggregations();
```
This returns an associative array with the requested aggregations. Bucket aggregations such as `histogram`, `range`, `date_range`, and `date_histogram` usually contain a `buckets` array. Metric aggregations such as `min`, `max`, `sum`, `avg`, and `median_absolute_deviation` return a `value`; percentile aggregations return `values`.

For example:

```php
$average = $result->getAggregations()['average_price']['value'];
```

Facets can be identified by their chosen alias.
Each facet is an array containing the faceted values and counts in the `buckets` array:

``` php
$facets = $results->getFacets();
$year_facet = $facets['_year'];
print_r($year_facet);

(
   [buckets] => Array
                (
                    [0] => Array
                        (
                            [key] => 1992
                            [doc_count] => 1
                        )
                    [1] => Array
                        (
                            [key] => 1986
                            [doc_count] => 1
                        )
                    [2] => Array
                        (
                            [key] => 1979
                            [doc_count] => 1
                        )
   )
)
```

When `facet_filter_mode` is `auto` or `max`, buckets can also contain a `status` value: `selected`, `available`, or `unavailable`.

## ChatResult object

[Search:chat](searchclass.md#chat) returns a `ChatResult` object:

```php
$result = $search
    ->chat('What is vector search?', 'docs', 'assistant')
    ->get();

echo $result->getAnswer();
$conversationUuid = $result->getConversationUuid();
```

The response fields are available through:

- `getConversationUuid()` - the existing or generated conversation UUID.
- `getUserQuery()` - the original user message.
- `getSearchQuery()` - the standalone query generated for retrieval.
- `getResponse()` or `getAnswer()` - the generated answer.
- `getSources()` - retrieved source rows decoded from the response's JSON string into an array.
- `getRawSources()` - the original `sources` JSON string.
- `getData()` - the complete decoded chat response.
- `getResponseObject()` - the underlying `Response` object.

Use the returned UUID in the fourth argument of the next `chat()` call to continue the conversation:

```php
$nextResult = $search
    ->chat(
        'Give me an example.',
        'docs',
        'assistant',
        $result->getConversationUuid()
    )
    ->get();
```

## ResultHit object

The `ResultHit` encapsulates the matched document provided in the search result set.

The document id can be retrieved with `getId()`

```php
foreach($result as $hit)
{
    $hit->getId();
}
```

Any document's field/attribute or expression defined as `source` can be retrieved directly as an object property:

```php
foreach($result as $hit)
{
    $hit->title;
    $hit->someattributename;
}
```
To get an array with all of them, use `getData()`:

```php
foreach($result as $hit)
{
    $hit->getData();
}
```

To get the calculated score for each document, use `getScore()`:


```php
foreach($result as $hit)
{
    $hit->getScore();
}
```

If the query performed highlighting, it can be retrieved with `getHighlight()`:

```php
foreach($result as $hit)
{
    $hit->getHighlight();
}
```
<!-- proofread -->

