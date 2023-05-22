# SQL
Allows running a SQL command.
For a complete reference of payload and response, see Manticore's [SQL API](https://manual.manticoresearch.com/Connecting_to_the_server/HTTP#/sql).
`body` must have the `query` parameter containing the desired SQL command.

Manticore Search below version 3.4 can only execute SELECT commands via `sql`.

```
$params = [
    'body' => [
        'query' => "SELECT * FROM movies where MATCH('@movie_title star trek')"
    ]
];

$response = $client->sql($params);
```
For non-SELECT commands, the `mode` parameter must be passed:

```
$params = [
    'mode' => 'raw',
    'body' => [
        'query' => "UPDATE movies set rating=7.0 WHERE id=10"
    ]
];

$response = $client->sql($params);
```
<!-- proofread -->