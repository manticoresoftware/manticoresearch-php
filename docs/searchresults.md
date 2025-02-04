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
$result->getFacets();
```
This returns an associative array with the requested facets, where a facet can be identified by the chosen facet alias.
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

