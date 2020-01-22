<?php


namespace Manticoresearch\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Endpoints\Pq\DeleteByQuery;
use Manticoresearch\Endpoints\Pq\Doc;

/**
 * Class Pq
 * @package Manticoresearch\Endpoints
 */
class Pq
{
    /**
     * @var Client
     */
    protected $_client;

    /**
     * Pq constructor.
     * @param $client
     */
    public function __construct($client)
    {
        $this->_client = $client;
    }

    /**
     * @param $params
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
        $response = $this->_client->request($endpoint);
        return $response->getResponse();
    }

    /**
     * @param $params
     * @return mixed
     */
    public function search($params)
    {
        $index = $params['index'] ?? null;
        $body = $params['body'];
        $endpoint = new \Manticoresearch\Endpoints\Pq\Search();
        $endpoint->setIndex($index);
        $endpoint->setQuery($params['query'] ?? null);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint);
        return $response->getResponse();
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
        $response = $this->_client->request($endpoint);
        return $response->getResponse();
    }
}