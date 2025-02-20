# Percolate result objects

## PercolateResultSet object

Returned by the [percolate()](tableclass.md#percolate) method, the PercolateResultSet object extends the [ResultSet](searchresults.md#resultset-object) by using [PercolateResultHit](#percolateresulthit-object) objects as elements instead of [ResultHit](searchresults.md#resulthit-object) objects.

## PercolateResultHit object

The PercolateResultHit object extends [ResultHit](searchresults.md#resulthit-object) and offers several additional methods:

- `getDocSlots()` returns an array that specifies the tables of documents from the array provided by [percolate()](tableclass.md#percolate) that match the current returned query.
- `getDocsMatches($docs)` filters the input document list with the doc slots of the current returned query. The document list must maintain the same tables as the list provided at [percolate()](tableclass.md#percolate).
- `getData()` returns the stored query that is provided.

   
 
 
## PercolateDocsResultSet object
 
Returned by the [percolateToDocs()](tableclass.md#percolatetodocs) method, this object implements `Iterator`, similar to the [PercolateResultSet](#percolateresultset-object). However, the constructor also requires the input document list used at the input of the [percolateToDocs()](tableclass.md#percolatetodocs) method. The iterated elements are [PercolateResultDoc](#percolateresultdoc-object) objects.
 
## PercolateResultDoc object

This is a simple object that holds a document array and an array with matched stored queries as [PercolateResultHit](#percolateresulthit-object) objects.

The `getData()` method returns the document.
 ```php
foreach($result as $row) {
  $row->getData();
}
```

The `getQueries` method returns the list of stored queries found to have matches for the document. The list can be empty.

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
The `hasQueries` method informs whether the list of stored queries is empty or not.

```php
foreach($result as $row) {
  $row->hasQueries();
}
```
<!-- proofread -->