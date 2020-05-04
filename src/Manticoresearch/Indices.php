<?php


namespace Manticoresearch;

use Manticoresearch\Endpoints\Indices\Alter;
use Manticoresearch\Endpoints\Indices\Create;
use Manticoresearch\Endpoints\Indices\Describe;
use Manticoresearch\Endpoints\Indices\Drop;
use Manticoresearch\Endpoints\Indices\FlushRamchunk;
use Manticoresearch\Endpoints\Indices\FlushRtindex;
use Manticoresearch\Endpoints\Indices\Import;
use Manticoresearch\Endpoints\Indices\Optimize;
use Manticoresearch\Endpoints\Indices\Settings;
use Manticoresearch\Endpoints\Indices\Status;
use Manticoresearch\Endpoints\Indices\Truncate;
use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;

class Indices
{
    use Utils;
    /**
     * @var Client
     */
    protected $_client;

    /**
     * @var array
     */
    protected $_params;

    /**
     * Pq constructor.
     * @param Client $client
     */
    public function __construct($client)
    {
        $this->_client = $client;
        $this->_params = ['responseClass' => 'Manticoresearch\\Response\\SqlToArray'];
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function alter($params)
    {
        $index = $params['index'] ?? null;
        $body = $params['body'];
        $endpoint = new Alter();
        $endpoint->setIndex($index);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint, $this->_params);
        return $response->getResponse();
    }


    /**
     *
     * @param array $params
     * @return mixed
     */
    public function create($params)
    {
        $index = $params['index'] ?? null;
        $body = $params['body'];
        $endpoint = new Create();
        $endpoint->setIndex($index);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint, $this->_params);
        return $response->getResponse();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function describe($params)
    {
        $index = $params['index'] ?? null;
        $body = $params['body'] ?? [];
        $endpoint = new Describe();
        $endpoint->setIndex($index);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint, $this->_params);
        return $response->getResponse();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function drop($params)
    {
        $index = $params['index'] ?? null;
        $body = $params['body'] ?? [];
        $endpoint = new Drop();
        $endpoint->setIndex($index);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint, $this->_params);
        return $response->getResponse();
    }
    /**
     * @param array $params
     * @return mixed
     */
    public function import($params)
    {
        $index = $params['index'] ?? null;
        $body = $params['body'] ?? [];
        $endpoint = new Import();
        $endpoint->setIndex($index);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint, $this->_params);
        return $response->getResponse();
    }
    /**
     * @param array $params
     * @return mixed
     */
    public function flushramchunk($params)
    {
        $index = $params['index'] ?? null;
        $endpoint = new FlushRamchunk();
        $endpoint->setIndex($index);
        $endpoint->setBody();
        $response = $this->_client->request($endpoint, $this->_params);
        return $response->getResponse();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function flushrtindex($params)
    {
        $index = $params['index'] ?? null;
        $endpoint = new FlushRtindex();
        $endpoint->setIndex($index);
        $endpoint->setBody();
        $response = $this->_client->request($endpoint, $this->_params);
        return $response->getResponse();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function optimize($params)
    {
        $index = $params['index'] ?? null;
        $body = $params['body'] ?? null;
        $endpoint = new Optimize();
        $endpoint->setIndex($index);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint, $this->_params);
        return $response->getResponse();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function status($params)
    {
        $index = $params['index'] ?? null;
        $body = $params['body'] ?? null;
        $endpoint = new Status();
        $endpoint->setIndex($index);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint, $this->_params);
        return $response->getResponse();
    }

    /**
     * @param array $params
     * @return array|mixed|string
     */
    public function settings($params)
    {
        $index = $params['index'] ?? null;
        $body = $params['body'] ?? null;
        $endpoint = new Settings();
        $endpoint->setIndex($index);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint, $this->_params);
        return $response->getResponse();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function truncate($params)
    {
        $index = $params['index'] ?? null;
        $body = $params['body'] ?? null;
        $endpoint = new Truncate();
        $endpoint->setIndex($index);
        $endpoint->setBody($body);
        $response = $this->_client->request($endpoint, $this->_params);
        return $response->getResponse();
    }
}
