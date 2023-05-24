# SQL
Allows running a SQL command.
For a complete reference of payload and response see Manticore's [SQL API](https://manual.manticoresearch.com/Connecting_to_the_server/HTTP#/sql).
Manticore Search below 3.4 can only execute SELECT commands via `sql`.

The `query` parameter must contain the desired SQL command to be executed:

```
$query = "SELECT * FROM movies where MATCH('@movie_title star trek')";
$response = $client->sql($query);
```

For non-SELECT commands, the `rawMode` parameter must be set to true:

```
$query = "SELECT * FROM movies where MATCH('@movie_title star trek')";
$rawMode = true;
$response = $client->sql($query, $rawMode);
```

Alternatively, you can pass function arguments as a single array:

```
$params = [
    'body' => [
        'query' => "SELECT * FROM movies where MATCH('@movie_title star trek')"
    ]
];

$response = $client->sql($params);
```
For non-SELECT commands, `mode` must be passed as 'raw':

```
$params = [
    'mode' => 'raw',
    'body' => [
        'query' => "UPDATE movies set rating=7.0 WHERE id=10"
    ]
];

$response = $client->sql($params);
```
