<?php

declare(strict_types=1);

namespace Manticoresearch;

use Manticoresearch\Connection\Strategy\Random;
use Manticoresearch\Connection\Strategy\RoundRobin;
use Manticoresearch\Endpoints\AbstractEndpoint;

use Manticoresearch\Endpoints\Pq;
use Manticoresearch\Exceptions\ConnectionException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Client
 * @package Manticoresearch
 * @category Manticoresearch
 * @author Adrian Nuta <adrian.nuta@manticoresearch.com>
 * @link https://manticoresearch.com
 */
class Client
{
    const VERSION = '1.0.0';

    public $transport;
    protected $_config = [];
    private $_connectionStrategy = RoundRobin::class;
    protected $_connectionPool;

    protected $_logger;

    public function __construct($config = [], LoggerInterface $logger = null)
    {
        $this->setConfig($config);
        $this->_logger = $logger ?? new NullLogger();
        $this->_initConnections();

    }

    protected function _initConnections()
    {
        $connections = [];
        if (isset($this->_config['connections'])) {
            foreach ($this->_config['connections'] as $connection) {
                $connections[] = Connection::create($connection);
            }

        }

        if (empty($connections)) {
            $connections[] = Connection::create($this->_config);
        }
        if (isset($this->_config['connectionStrategy'])) {

            $strategyName = '\\Manticoresearch\\Connection\\Strategy\\' . $this->_config['connectionStrategy'];
            $strategy = new $strategyName();
        } else {
            $strategy = new $this->_connectionStrategy;
        }

        $this->_connectionPool = new Connection\ConnectionPool($connections, $strategy);
    }
    public function setHosts($hosts)
    {
        $this->_config['connections'] = $hosts;
        $this->_initConnections();
    }
    public function setConfig(array $config)
    {
        $this->_config = array_merge($this->_config, $config);
        return $this;
    }

    public static function create($config): Client
    {
        return self::createFromArray($config);
    }

    public static function createFromArray($config)
    {

        return new self($config);
    }

    public function getConnections()
    {
        return $this->_connectionPool->getConnections();
    }
    public function getConnectionPool()
    {
        return $this->_connectionPool;
    }
    /**
     * Endpoint: search
     * @param array $params
     */
    public function search(array $params = [])
    {


        $endpoint = new Endpoints\Search($params);
        $response = $this->request($endpoint);

        return $response->getResponse();
    }

    /**
     * Endpoint: insert
     * @param array $params
     */
    public function insert(array $params = [])
    {

        $endpoint = new Endpoints\Insert($params);
        $response = $this->request($endpoint);

        return $response->getResponse();
    }

    /**
     * Endpoint: replace
     * @param array $params
     */
    public function replace(array $params = [])
    {

        $endpoint = new Endpoints\Replace($params);
        $response = $this->request($endpoint);

        return $response->getResponse();
    }

    /**
     * Endpoint: update
     * @param array $params
     */
    public function update(array $params = [])
    {

        $endpoint = new Endpoints\Update($params);
        $response = $this->request($endpoint);

        return $response->getResponse();
    }

    /**
     * Endpoint: sql
     * @param array $params
     */
    public function sql(array $params = [])
    {
        $endpoint = new Endpoints\Sql($params);
        $response = $this->request($endpoint);
        return $response->getResponse();
    }

    /**
     * Endpoint: delete
     * @param array $params
     * @return array
     */
    public function delete(array $params = [])
    {

        $endpoint = new Endpoints\Delete($params);
        $response = $this->request($endpoint);

        return $response->getResponse();
    }

    /**
     * Endpoint: pq
     * @param array $params
     */
    public function pq(array $params = []): Pq
    {

        return new Pq($this);
    }

    /**
     * Endpoint: bulk
     * @param array $params
     * @return array
     */
    public function bulk(array $params = [])
    {
        $endpoint = new Endpoints\Bulk($params);
        $response = $this->request($endpoint);

        return $response->getResponse();
    }

    /*
     * @return callable|array
     */

    public function request(Request $request)
    {

        $connection = $this->_connectionPool->getConnection();

        try {
            $response = $connection->getTransportHandler()->execute($request);
        } catch (ConnectionException $e) {
            //@todo implement retry
            $this->_logger->error('Manticore Search Request failed', [
                'exception' => $e,
                'request' => $e->getRequest()
            ]);

            $connection->mark(false);
            if (!$this->_connectionPool->hasConnections()) {
                throw $e;
            }
            return $this->request($request);

        }

        //@todo implement logger debug message
        return $response;
    }


}