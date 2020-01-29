# Configuration


### Client configuration


The Client() class accepts a configuration as an array. 
Without any configuration provided, it tries to connect using HTTP `127.0.0.1` on port `9306`.


#### config paramaters


*  host -  IP or DNS of server (part of a connection)
*  port -   HTTP API port (part of a connection)
*  connections - list of connections
*  connectionStrategy - name of connection pool strategy, default is `RoundRobin`
*  transport -  transport class name, default `Http` (part of a connection)

If there is a single connection used, it can be defined directly in the configuration array.

If multiple connections can used, they must be defined in `connections` array. 

#### Transport

Implemented transports:

* Http -  uses CURL extension
* Https  -  uses CURL extension, for https hosts
* PhpHttp - adapter for HTTPlug 1.0. A client and a message factory must be present in the environment.

Http/Https adapters options:

*  timeout -  connection timeout
*  connection_timeout - connection connect timeout
*  proxy  -  proxy definition as  host:port
*  username - username for HTTP Auth
*  password - password for HTTP Auth
*  headers - array of custom headers
*  curl - array of CURL settings as option=>value 
*  persistent -  define whenever connection is persistent or not, boolean value

Example:
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


* Random - each query performed will use randomly one of the defined connections
* RoundRobin -  each query performed picks an alive connection in round robin fashion  
* StaticRoundRobin - connection are picked in round robin, but a connection is reused as long as it's alive. For example on first query connection 1 will be picked. If the connection works, query 2 will also use it and so on until the connection ends with hard error. In this case next queries will try to use the next connection from the pool (default)

On all strategies if a connection returns a hard error it will not be used in future attempts.

Custom strategy can be provided to the `connectionStrategy`. They must implement `Manticoresearch\Connection\Strategy\SelectorInterface`

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