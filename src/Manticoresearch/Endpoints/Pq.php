<?php


namespace Manticoresearch\Endpoints;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Pq\DeleteByQuery;
use Manticoresearch\Endpoints\Pq\Doc;
use phpDocumentor\Reflection\Types\Object_;

/**
 * Class Pq
 * @package Manticoresearch\Endpoints
 */
class Pq
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Pq constructor.
     * @param Client $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function doc($params)
    {
        $index = $params['index'] ?? null;
        $id = $params['id'] ?? null;

        $body = $params['body'];
        $endpoint = new Doc();
        $endpoint->setIndex($index);
        $endpoint->setId($id);
        $endpoint->setQuery($params['query'] ?? null);
        $endpoint->setBody($body);
        $response = $this->client->request($endpoint);
        return $response->getResponse();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function search($params, $obj = false)
    {
        $index = $params['index'] ?? null;
        $body = $params['body'];
        $endpoint = new \Manticoresearch\Endpoints\Pq\Search();
        $endpoint->setIndex($index);
        $endpoint->setQuery($params['query'] ?? null);
        $endpoint->setBody($body);
        $response = $this->client->request($endpoint);
        if ($obj === true) {
            return $response;
        } else {
            return $response->getResponse();
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function deleteByQuery($params = [])
    {
        $index = $params['index'] ?? null;
        $body = $params['body'];
        $endpoint = new DeleteByQuery();
        $endpoint->setIndex($index);
        $endpoint->setQuery($params['query'] ?? null);
        $endpoint->setBody($body);
        $response = $this->client->request($endpoint);
        return $response->getResponse();
    }
}
