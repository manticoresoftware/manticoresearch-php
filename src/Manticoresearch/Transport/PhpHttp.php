<?php


namespace Manticoresearch\Transport;

use Http\Discovery\MessageFactoryDiscovery;

use Manticoresearch\Connection;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Request;
use Manticoresearch\Response;
use Manticoresearch\Transport;

use Http\Discovery\HttpClientDiscovery;
use Psr\Log\LoggerInterface;

/**
 * Class PhpHttp
 * @package Manticoresearch\Transport
 */
class PhpHttp extends Transport implements TransportInterface
{
    protected $client;
    protected $messageFactory;
    /**
     * PhpHttp constructor.
     * @param Connection|null $connection
     * @param LoggerInterface|null $logger
     */

    public function __construct(Connection $connection = null, LoggerInterface $logger = null)
    {
        if (!class_exists(HttpClientDiscovery::class) || !class_exists(MessageFactoryDiscovery::class)) {
            throw new \LogicException('You cannot use the "' . self::class . '" '
                . 'as the "php-http/discovery" package is not installed. '
                . 'Try running "composer require php-http/discovery".');
        }
        $this->client = HttpClientDiscovery::find();
        $this->messageFactory = MessageFactoryDiscovery::find();
        parent::__construct($connection, $logger);
    }

    public function execute(Request $request, $params = [])
    {
        $connection = $this->getConnection();

        $url = $this->connection->getConfig('scheme') . '://' . $connection->getHost() . ':' . $connection->getPort()
            . $connection->getPath();
        $endpoint = $request->getPath();
        $url .= $endpoint;
        $url = $this->setupURI($url, $request->getQuery());
        $method = $request->getMethod();

        $headers = $connection->getHeaders();
        $headers['Content-Type'] = $request->getContentType();
        $data = $request->getBody();
        if (!empty($data)) {
            if (is_array($data)) {
                $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
            } else {
                $content = $data;
            }
        } else {
            $content = '';
        }
        $start = microtime(true);
        $message = $this->messageFactory->createRequest($method, $url, $headers, $content);
        try {
            $responsePSR = $this->client->sendRequest($message);
        } catch (\Exception $e) {
            throw new ConnectionException($e->getMessage(), $request);
        }
        $end = microtime(true);
        $status = $responsePSR->getStatusCode();
        $responseString = $responsePSR->getBody();

        if (isset($params['responseClass'])) {
            $responseClass = $params['responseClass'];
            $responseClassParams = isset($params['responseClassParams'])?$params['responseClassParams']:[];
            $response = new $responseClass($responseString, $status, $responseClassParams);
        } else {
            $response = new Response($responseString, $status);
        }

        $time = $end-$start;
        $response->setTime($time);
        $response->setTransportInfo([
            'url' => $url,
            'headers' => $headers,
            'body' => $request->getBody()
        ]);
        $this->logger->debug('Request body:', [
            'connection' => $connection->getConfig(),
            'payload'=> $request->getBody()
        ]);
        $this->logger->info('Request:', [
            'url' => $url,
            'status' => $status,
            'time' => $time,
        ]);
        $this->logger->debug('Response body:', $response->getResponse());

        if ($response->hasError()) {
            $this->logger->error('Response:', [
                'url' => $url,
                'error' => $response->getError(),
                'payload' => $request->getBody(),
            ]);
            throw new ResponseException($request, $response);
        }
        return $response;
    }
}
