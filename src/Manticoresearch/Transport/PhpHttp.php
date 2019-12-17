<?php


namespace Manticoresearch\Transport;


use Http\Discovery\MessageFactoryDiscovery;

use Manticoresearch\Connection;
use Manticoresearch\Request;
use Manticoresearch\Response;
use Manticoresearch\Transport;

use Http\Discovery\HttpClientDiscovery;


class PhpHttp extends Transport implements TransportInterface
{

    public function __construct(Connection $connection = null)
    {
        $this->client = HttpClientDiscovery::find();
        $this->messageFactory = MessageFactoryDiscovery::find();
        parent::__construct($connection);
    }

    public function execute(Request $request,$params=[])
    {
        $connection = $this->getConnection();

        $url = $this->_connection->getConfig('scheme') . '://' . $connection->getHost() . ':' . $connection->getPort();
        $endpoint = $request->getPath();
        $url .= $endpoint;
        $url = $this->setupURI($url, $request->getQuery());
        $method = $request->getMethod();

        $headers = $connection->getHeaders();
        array_push($headers, sprintf('Content-Type: %s', $request->getContentType()));
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
        $message = $this->messageFactory->createRequest($method, $url, $headers, $content);
        $responsePSR = $this->client->sendRequest($message);
        $response = new Response($responsePSR->getBody(), $responsePSR->getStatusCode());
        return $response;
    }
}