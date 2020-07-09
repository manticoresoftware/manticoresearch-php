# Percolate result objects

## PercolateResultSet object

Returned by [percolate()](indexclass.md#percolate) method. It extends [ResultSet](searchresults.md#resultset-object)
object by using [PercolateResultHit](#percolateresulthit-object) objects as elements instead of 
[ResultHit](searchresults.md#resulthit-object) objects.



## PercolateResultHit object

It extends [ResultHit](searchresults.md#resulthit-object) by providing several additional methods:

`getDocSlots()` returns the array that specify the indexes of  documents from the array provided by
 [percolate()](indexclass.md#percolate) that provide match for the current returned query.
 
 `getDocsMatches($docs)` filters the list of documents at input with the doc slots of the current returned query.
The document list must preserve exactly the same indexes of the list provided at [percolate()](indexclass.md#percolate).

`getData()` will provide the stored query that is returned.
   
 
 
 ## PercolateDocsResultSet object
 
 Returned by [percolateToDocs()](indexclass.md#percolatetodocs)  method.
 It implements an `Iterator` just like [PercolateResultSet](#percolateresultset-object) but the constructor also 
 requires the input documents list used at input of  [percolateToDocs()](indexclass.md#percolatetodocs) method.
 The iterated elements are [PercolateResultDoc](#percolateresultdoc-object) objects.
 
 
 
 
 ## PercolateResultDoc object
 
It's a simple object that holds a document array and an array with stored matched queries as 
[PercolateResultHit](#percolateresulthit-object) objects;
 
 `getData()` method will return the document.
 ```php
foreach($result as $row) {
  $row->getData();
}
```

`getQueries` method return the list of stored queries found to have matches for the document. 
The list can be empty.

```php
// $result is PercolateDocsResultSet
foreach($result as $row) {
  // $row is PercolateResultDoc
  foreach($row->getQueries as $query) {
       // $query is PercolateResultHit
       $query->getData();
  }
}
```
`hasQueries` informs whenever the list of stored queries is empty or not.

```php
foreach($result as $row) {
  $row->hasQueries();
}
```