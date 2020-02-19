<?php


namespace Manticoresearch;


use Manticoresearch\Endpoints\Cluster\Alter;
use Manticoresearch\Endpoints\Cluster\Create;
use Manticoresearch\Endpoints\Cluster\Delete;
use Manticoresearch\Endpoints\Cluster\Join;
use Manticoresearch\Endpoints\Cluster\Set;

class Cluster
{
    /**
     * @var Client
     */
    protected $_client;
    protected $_params;

    /**
     * Pq constructor.
     * @param $client
     */
    public function __construct($client)
    {
        $this->_client = $client;
        $this->_params =['responseClass'=>'Manticoresearch\\Response\\SqlToArray'];

    }

    public function alter($params)
    {
        $cluster = $params['cluster'] ?? null;
        $body = $params['body'];
        $endpoint = new Alter();
        $endpoint->setCluster($cluster);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint,$this->_params);
        return  $response->getResponse();

    }

    public function create($params)
    {
        $cluster = $params['cluster'] ?? null;
        $body = $params['body'];
        $endpoint = new Create();
        $endpoint->setCluster($cluster);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint,$this->_params);
        return  $response->getResponse();

    }

    public function delete($params)
    {
        $cluster = $params['cluster'] ?? null;
        $body = $params['body'];
        $endpoint = new Delete();
        $endpoint->setCluster($cluster);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint,$this->_params);
        return  $response->getResponse();

    }

    public function join($params)
    {
        $cluster = $params['cluster'] ?? null;
        $body = $params['body'];
        $endpoint = new Join();
        $endpoint->setCluster($cluster);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint,$this->_params);
        return  $response->getResponse();

    }

    public function set($params)
    {
        $cluster = $params['cluster'] ?? null;
        $body = $params['body'];
        $endpoint = new Set();
        $endpoint->setCluster($cluster);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint,$this->_params);
        return  $response->getResponse();

    }
}