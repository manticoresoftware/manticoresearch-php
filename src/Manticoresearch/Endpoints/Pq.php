<?php


namespace Manticoresearch\Endpoints;


use Manticoresearch\Endpoints\Pq\DeleteByQuery;
use Manticoresearch\Endpoints\Pq\Doc;

class Pq
{
    protected $_client;

    public function __construct($client)
    {
        $this->_client = $client;
    }

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