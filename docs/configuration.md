# Configuration


### Client configuration


The [Client()](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Client.html) class accepts a configuration as an array. By default, without any configuration provided, it attempts to connect to `127.0.0.1` on port `9308`.


#### config paramaters


* host - IP or DNS of the server (part of a connection)
* port - HTTP API port (part of a connection)
* connections - array of connections
* connectionStrategy - name of [connection pool strategy](#connection-pool-strategies), default is `StaticRoundRobin`
* transport - [transport](#transport) class name or object, default `Http` (part of a connection)
* retries - number of [retries](#retries) to perform in case of hard failure

If using a single connection, it can be defined directly in the configuration array like:

```php
   $config = ['host'=>'127.0.0.1','port'=>9308];
   $client = new \Manticoresearch\Client($config);
```

If multiple connections are used, they must be defined in the `connections` array (see below).

A connection array can contain:

* host - IP or DNS of the server
* port - HTTP API port
* transport - transport class name or object, default `Http`

Additionally, the connection array can include various options for the transport adapter.


#### Transport

Implemented transports:

* [Http](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Transport.Http.html) - uses the CURL extension
* [Https](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Transport.Https.html) - uses the CURL extension for HTTPS hosts
* [PhpHttp](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Transport.PhpHttp.html) - adapter for HTTPlug 1.0. A client and a message factory must be present in the environment.

Http/Https adapter options:

* timeout - connection timeout
* connection_timeout - connection connect timeout
* proxy - proxy definition as host:port
* username - username for HTTP Auth
* password - password for HTTP Auth
* headers - array of custom headers
* curl - array of CURL settings as option=>value
* persistent - define whether the connection is persistent or not

Simple example of multiple hosts:
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
                    'persistent' => true
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

Multiple hosts example:

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

Single host example:

```
        $params = ['host' => '123.0.0.1', 'port' => '1234', 'retries' => 2];
        $client =  new Client($params);
```
<!-- proofread -->