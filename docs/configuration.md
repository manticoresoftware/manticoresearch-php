# Configuration


### Client configuration


The [Client()](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Client.html) class accepts a configuration as an array. By default, without any configuration provided, it attempts to connect to `127.0.0.1` on port `9308`.


#### config paramaters


* host - IP or DNS of the server (part of a connection)
* port - HTTP API port (part of a connection)
* connections - array of connections
* connectionStrategy - name of [connection pool strategy](#connection-pool-strategies), default is `StaticRoundRobin`
* transport - [transport](#transport) class name or object, default is `Http` (part of a connection)
* retries - number of [retries](#retries) to perform in case of hard failure

If using a single connection, it can be defined directly in the configuration array like:

```php
   $config = ['host'=>'127.0.0.1','port'=>9308];
   $client = new \Manticoresearch\Client($config);
```

If multiple connections are used, they must be defined in the `connections` array (see below).  By default, the client uses a single connection with host=127.0.0.1 and port=9308, respectively.

A connection array can contain:

* host - IP or DNS of the server
* port - HTTP API port
* transport - transport class name or object, default is `Http`

Additionally, the connection array can include various options for the transport adapter.


#### Transport

Implemented transports:

* [Http](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Transport.Http.html) - uses the CURL extension
* [Https](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Transport.Https.html) - uses the CURL extension for HTTPS hosts
* [PhpHttp](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Transport.PhpHttp.html) - adapter for HTTPlug 1.0. A client and a message factory must be present in the environment.

Connection and transport options:

* timeout - query timeout
* connection_timeout - connection timeout
* proxy - proxy definition as host:port
* username - username for HTTP Basic authentication
* password - password for HTTP Basic authentication
* bearer_token - bearer token used in the `Authorization` header
* headers - array of custom headers
* curl - array of CURL settings as option=>value
* persistent - define whether the connection is persistent or not
* bigint_to_string - define whether big integers in response are converted to strings or not

#### Authentication

All HTTP transports support Basic and bearer-token authentication.

For HTTP Basic authentication, provide both `username` and `password`:

```php
$client = new \Manticoresearch\Client([
    'host' => '127.0.0.1',
    'port' => 9308,
    'username' => 'admin',
    'password' => 'StrongPass#2026',
]);
```

For bearer authentication, provide `bearer_token`:

```php
$client = new \Manticoresearch\Client([
    'host' => '127.0.0.1',
    'port' => 9308,
    'bearer_token' => '0123456789abcdef...',
]);
```

Basic credentials and `bearer_token` are mutually exclusive on the same connection. Configuring both raises a `RuntimeException`. When authentication is configured, its generated `Authorization` header replaces a custom header with the same name.

To create or rotate a bearer token for the authenticated user, first connect with Basic authentication and call `token()`:

```php
$basicClient = new \Manticoresearch\Client([
    'host' => '127.0.0.1',
    'port' => 9308,
    'username' => 'admin',
    'password' => 'StrongPass#2026',
]);

$token = $basicClient->token();

$client = new \Manticoresearch\Client([
    'host' => '127.0.0.1',
    'port' => 9308,
    'bearer_token' => $token,
]);
```

`token()` sends `POST /token` with an empty JSON object and returns the raw token string. Manticore returns the raw token only once, so store it securely.

See Manticore's [authentication and authorization documentation](https://manual.manticoresearch.com/Security/Authentication_and_authorization) for server setup, users, and permissions.

A simple example of multiple hosts:
```
        $params = ['connections'=>
            [
                [
                    'host' => '123.0.0.1',
                    'port' => '1234',
                ],
                [
                    'host' => '123.0.0.2',
                    'port' => '1235',
                ],

            ]
        ];
        $client =  new Client($params);
```


A more advanced example where one host uses HTTP authentication and another requires SSL:

```
        $params = ['connections'=>
            [
                [
                    'host' => '123.0.0.1',
                    'port' => '1234',
                    'timeout' => 5,
                    'connection_timeout' => 1,
                    'proxy' => '127.0.0.255',
                    'username' => 'test',
                    'password' => 'secret',
                    'headers' => [
                        'X-Forwarded-Host' => 'mydev.domain.com'
                    ],
                    'curl' => [
                        CURLOPT_FAILONERROR => true
                    ],
                    'persistent' => true,
                    'bigint_to_string' => true,
                ],
                [
                    'host' => '123.0.0.2',
                    'port' => '1235',
                    'timeout' => 5,
                    'transport' => 'Https',
                    'curl' =>[
                        CURLOPT_CAPATH => 'path/to/my/ca/folder',
                        CURLOPT_SSL_VERIFYPEER => true
                    ],
                    'connection_timeout' => 1,
                    'persistent' => true
                ],

            ]
        ];
        $client =  new Client($params);
```

#### Connection pool strategies


* [Random](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Connection.Strategy.Random.html) - each query performed will randomly use one of the defined connections
* [RoundRobin](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Connection.Strategy.RoundRobin.html) - each query performed picks an alive connection in a round-robin fashion
* [StaticRoundRobin](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Connection.Strategy.StaticRoundRobin.html) - connections are picked in round-robin order, but a connection is reused as long as it's alive. For example, on the first query, connection 1 will be picked. If the connection works, query 2 will also use it, and so on until the connection encounters a hard error. In this case, the next queries will try to use the next connection from the pool (default)

In all strategies, if a connection returns a hard error, it will not be used in future attempts.

A custom strategy can be provided to the `connectionStrategy`. They must implement the [SelectorInterface](https://manticoresoftware.github.io/manticoresearch-php/interface-Manticoresearch.Connection.Strategy.SelectorInterface.html)

```
$params = [
    'host' => '127.0.0.1',
    'post' => 9308,
    'connectionStrategy' => MyCustomStrategy::class
]
```
or 
```
$params = [
    'host' => '127.0.0.1',
    'post' => 9308,
    'connectionStrategy' => new MyCustomStrategy()
]
```

### Retries

By default, the number of retries is equal to the number of defined hosts.

If the number of hosts is 10 and retries are set to 5, the query will retry on 5 hosts according to the connection strategy and end with an error after 5 attempts.

A multiple hosts example:

```
        $params = ['connections'=>
            [
                ['host' => '123.0.0.1', 'port' => '1234'],
                ['host' => '123.0.0.2', 'port' => '1235'],
                ['host' => '123.0.0.2', 'port' => '1236'],
            ],
            'retries' => 2
        ];
        $client =  new Client($params);
```

A single host example:

```
        $params = ['host' => '123.0.0.1', 'port' => '1234', 'retries' => 2];
        $client =  new Client($params);
```
<!-- proofread -->