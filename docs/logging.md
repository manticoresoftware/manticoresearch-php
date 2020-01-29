# Logging

The Manticore Search PHP client supports compatible PSR Loggers.
There are several where DEBUG,INFO or ERROR messages are logged.

The logger must be passed at the Client() initialization. 
In absence of a defined logger, the NullLoger is used.

## Using with Monolog

```
   $ composer require monolog/monolog

```

```
   require_once __DIR__ . '/vendor/autoload.php';
   use Manticoresearch\Client;
   use Monolog\Logger;
   use Monolog\Handler\StreamHandler;
   
   $logger = new Logger('name');
   $logger->pushHandler(new StreamHandler('/my/log.file',Logger::INFO));
   $config = ['host'=>'127.0.0.1', 'port'=>9306];
   $client = new  Client($config,$logger);
```