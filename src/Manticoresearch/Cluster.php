<?php


namespace Manticoresearch;

use Manticoresearch\Endpoints\Cluster\Alter;
use Manticoresearch\Endpoints\Cluster\Create;
use Manticoresearch\Endpoints\Cluster\Delete;
use Manticoresearch\Endpoints\Cluster\Join;
use Manticoresearch\Endpoints\Cluster\Set;
use Manticoresearch\Response\SqlToArray;

class Cluster
{
    /**
     * @var Client
     */
    protected $client;
    protected $params;

    /**
     * Pq constructor.
     * @param Client $client
     */
    public function __construct($client)
    {
        $this->client = $client;
        $this->params =['responseClass'=> SqlToArray::class];
    }

    public function alter($params)
    {
        $cluster = $params['cluster'] ?? null;
        $body = $params['body'];
        $endpoint = new Alter();
        $endpoint->setCluster($cluster);
        $endpoint->setBody($body);
        $response = $this->client->request(
            $endpoint,
            array_merge($this->params, ['responseClassParams' => ['customMapping' => true]])
        );
        return  $response->getResponse();
    }

    public function create($params)
    {
        $cluster = $params['cluster'] ?? null;
        $body = $params['body'];
        $endpoint = new Create();
        $endpoint->setCluster($cluster);
        $endpoint->setBody($body);
        $response = $this->client->request($endpoint, $this->params);
        return  $response->getResponse();
    }

    public function delete($params)
    {
        $cluster = $params['cluster'] ?? null;
        $body = $params['body'];
        $endpoint = new Delete();
        $endpoint->setCluster($cluster);
        $endpoint->setBody($body);
        $response = $this->client->request($endpoint, $this->params);
        return  $response->getResponse();
    }

    public function join($params)
    {
        $cluster = $params['cluster'] ?? null;
        $body = $params['body'];
        $endpoint = new Join();
        $endpoint->setCluster($cluster);
        $endpoint->setBody($body);
        $response = $this->client->request($endpoint, $this->params);
        return  $response->getResponse();
    }

    public function set($params)
    {
        $cluster = $params['cluster'] ?? null;
        $body = $params['body'];
        $endpoint = new Set();
        $endpoint->setCluster($cluster);
        $endpoint->setBody($body);
        $response = $this->client->request($endpoint, $this->params);
        return  $response->getResponse();
    }
}
