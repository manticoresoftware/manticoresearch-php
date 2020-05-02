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
        $this->client = HttpClientDiscovery::find();
        $this->messageFactory = MessageFactoryDiscovery::find();
        parent::__construct($connection, $logger);
    }

    /**
     * @param Request $request
     * @param array $params
     * @return Response
     * @throws \Http\Client\Exception
     */
    public function execute(Request $request, $params=[])
    {
        $connection = $this->getConnection();

        $url = $this->_connection->getConfig('scheme') . '://' . $connection->getHost() . ':' . $connection->getPort();
        $endpoint = $request->getPath();
        $url .= $endpoint;
        $url = $this->setupURI($url, $request->getQuery());
        $method = $request->getMethod();

        $headers = $connection->getHeaders();
        $headers['Content-Type'] = $request->getContentType();
        $data = $request->getBody();
        if (!empty($data)) {
            if (is_array($data)) {
                $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
            $response = new $responseClass($responseString, $status);
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
        $this->_logger->debug('Request body:', [
            'connection' => $connection->getConfig(),
            'payload'=> $request->getBody()
        ]);
        $this->_logger->info(
            'Request:',
            [
                 'url' => $url,
                'status' => $status,
                'time' => $time
            ]
        );
        $this->_logger->debug('Response body:', $response->getResponse());

        if ($response->hasError()) {
            $this->_logger->error('Response error:', [$response->getError()]);
            throw new ResponseException($request, $response);
        }
        return $response;
    }
}
