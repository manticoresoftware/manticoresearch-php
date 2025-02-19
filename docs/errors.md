# Errors and exceptions

The client throws several types of exceptions. All are under the namespace ``Manticoresearch\Exceptions``.

## NoMoreNodesException

Thrown when nodes fail with hard errors and no retries are left.

## ResponseException

Soft errors returned by nodes. For example, when trying to insert a document with an ID that already exists or when trying to create a table that already exists.



## RuntimeException

Thrown when a mandatory parameter of the request payload is not present. For example, if the table is not set when trying to add a new document.

<!-- proofread -->