# Configuration


### Client configuration


The [Client()](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Client.html) class accepts a configuration as an array. 
Without any configuration provided, it tries to connect using HTTP `127.0.0.1` on port `9308`.


#### config paramaters


*  host -  IP or DNS of server (part of a connection)
*  port -   HTTP API port (part of a connection)
*  connections - list of connections
*  connectionStrategy - name of connection pool strategy, default is `StaticRoundRobin`
*  transport -  transport class name, default `Http` (part of a connection)
*  retries - number of retries to perform in case of hard failure 

If there is a single connection used, it can be defined directly in the configuration array like:

```php
   $config = ['host'=>'127.0.0.1','port'=>9308];
   $client = new \Manticoresearch\Client($config);
```

If multiple connections can used, they must be defined in `connections` array (see below). 

#### Transport

Implemented transports:

* [Http](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Transport.Http.html) -  uses CURL extension
* [Https](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Transport.Https.html)  -  uses CURL extension, for https hosts
* [PhpHttp](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Transport.PhpHttp.html) - adapter for HTTPlug 1.0. A client and a message factory must be present in the environment.

Http/Https adapters options:

*  timeout -  connection timeout
*  connection_timeout - connection connect timeout
*  proxy  -  proxy definition as  host:port
*  username - username for HTTP Auth
*  password - password for HTTP Auth
*  headers - array of custom headers
*  curl - array of CURL settings as option=>value 
*  persistent -  define whenever connection is persistent or not

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


A mode advanced example where one host uses http auth and another requires SSL:

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


* [Random](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Connection.Strategy.Random.html) - each query performed will use randomly one of the defined connections
* [RoundRobin](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Connection.Strategy.RoundRobin.html) -  each query performed picks an alive connection in round robin fashion  
* [StaticRoundRobin](https://manticoresoftware.github.io/manticoresearch-php/class-Manticoresearch.Connection.Strategy.StaticRoundRobin.html) - connection are picked in round robin, but a connection is reused as long as it's alive. For example on first query connection 1 will be picked. If the connection works, query 2 will also use it and so on until the connection ends with hard error. In this case next queries will try to use the next connection from the pool (default)

On all strategies if a connection returns a hard error it will not be used in future attempts.

Custom strategy can be provided to the `connectionStrategy`. They must implement [SelectorInterface](https://manticoresoftware.github.io/manticoresearch-php/interface-Manticoresearch.Connection.Strategy.SelectorInterface.html)

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

By default the number of retries is equal with the number of defined hosts. 

If the number of hosts is 10 and retries 5, the query will retry on 5 hosts according to connection strategy and end with error after 5 attempts. 

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
