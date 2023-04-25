# Logging

The Manticore Search PHP client supports compatible PSR Loggers.
There are several levels, including DEBUG, INFO, or ERROR messages being logged.

The logger must be passed at the Client() initialization. 
In the absence of a defined logger, the NullLogger is used.

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
<!-- proofread -->