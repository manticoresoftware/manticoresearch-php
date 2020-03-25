# Errors and exceptions

The client throws several types of exceptions:

## NoMoreNodesException

When nodes fail with hard errors and no retries are left

## ResponseException

Soft errors returned by nodes. For example when trying to insert a document with an id that already exists or when trying to create an index that already exists.



## RuntimeException

It can be thrown when a mandatory parameter of the request payload is not present. For example if index is not set when trying to add a new document.