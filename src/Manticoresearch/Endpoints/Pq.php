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
        $index = $params['index'];
        $id = null;
        if(isset($params['id'])) {
            $id = $params['id'];
        }

        $body = $params['body'];
        $endpoint = new Doc();
        $endpoint->setIndex($index);
        $endpoint->setId($id);
        $endpoint->setQuery($params['query']);
        $endpoint->setBody($body);
        $response =   $this->_client->request($endpoint);
        return $response->getResponse();
    }
    public function search($params)
    {
        $index = $params['index'];
        $body = $params['body'];
        $endpoint = new \Manticoresearch\Endpoints\Pq\Search();
        $endpoint->setIndex($index);
        $endpoint->setQuery($params['query']);
        $endpoint->setBody($body);
        $response =   $this->_client->request($endpoint);
        return $response->getResponse();
    }
    public function deleteByQuery($params =[])
    {
        $index = $params['index'];
        $body = $params['body'];
        $endpoint = new DeleteByQuery();
        $endpoint->setIndex($index);
        $endpoint->setQuery($params['query']);
        $endpoint->setBody($body);
        $response =   $this->_client->request($endpoint);
        return $response->getResponse();
    }
}