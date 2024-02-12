<?php

declare(strict_types=1);

namespace Manticoresearch;

use Manticoresearch\Connection\ConnectionPool;
use Manticoresearch\Connection\Strategy\SelectorInterface;
use Manticoresearch\Connection\Strategy\StaticRoundRobin;

use Manticoresearch\Endpoints\Pq;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Exceptions\NoMoreNodesException;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Response\SqlToArray;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Manticore  client object
 * @package Manticoresearch
 * @category Manticoresearch
 * @author Adrian Nuta <adrian.nuta@manticoresearch.com>
 * @link https://manticoresearch.com
 */
class Client
{
	/**
	 *
	 */
	const VERSION = '1.0.0';

	/**
	 * @var array
	 */
	protected $config = [];
	/**
	 * @var string
	 */
	private $connectionStrategy = StaticRoundRobin::class;
	/**
	 * @var ConnectionPool
	 */
	protected $connectionPool;

	/**
	 * @var LoggerInterface|NullLogger
	 */
	protected $logger;

	protected $lastResponse;

	/*
	 * $config can be a connection array or
	 * $config['connections] = array of connections
	 * $config['connectionStrategy'] = class name of pool strategy
	 */
	public function __construct($config = [], LoggerInterface $logger = null) {
		$this->setConfig($config);
		$this->logger = $logger ?? new NullLogger();
		$this->initConnections();
	}

	protected function initConnections() {
		$connections = [];
		if (isset($this->config['connections'])) {
			foreach ($this->config['connections'] as $connection) {
				if (is_array($connection)) {
					$connections[] = Connection::create($connection);
				} else {
					$connections[] = $connection;
				}
			}
		}

		if (empty($connections)) {
			$connections[] = Connection::create($this->config);
		}
		if (isset($this->config['connectionStrategy'])) {
			if (is_string($this->config['connectionStrategy'])) {
				$strategyName = $this->config['connectionStrategy'];
				if (strncmp($strategyName, 'Manticoresearch\\', 16) === 0) {
					$strategy = new $strategyName();
				} else {
					$strategyFullName = '\\Manticoresearch\\Connection\\Strategy\\' . $strategyName;
					if (class_exists($strategyFullName)) {
						$strategy = new $strategyFullName();
					} elseif (class_exists($strategyName)) {
						$strategy = new $strategyName();
					}
				}
			} elseif ($this->config['connectionStrategy'] instanceof SelectorInterface) {
				$strategy = $this->config['connectionStrategy'];
			} else {
				throw new RuntimeException('Cannot create a strategy based on provided settings!');
			}
		} else {
			$strategy = new $this->connectionStrategy;
		}
		if (!isset($this->config['retries'])) {
			$this->config['retries'] = sizeof($connections);
		}
		$this->connectionPool = new Connection\ConnectionPool(
			$connections,
			$strategy ?? new $this->connectionStrategy,
			$this->config['retries']
		);
	}

	/**
	 * @param string|array $hosts
	 */
	public function setHosts($hosts) {
		$this->config['connections'] = $hosts;
		$this->initConnections();
	}

	/**
	 * @param array $config
	 * @return $this
	 */
	public function setConfig(array $config): self {
		$this->config = array_merge($this->config, $config);
		return $this;
	}

	/**
	 * @param array $config
	 * @return Client
	 */
	public static function create($config): Client {
		return static::createFromArray($config);
	}

	/**
	 * @param array $config
	 * @return Client
	 */
	public static function createFromArray($config): Client {
		return new self($config);
	}

	/**
	 * @return mixed
	 */
	public function getConnections() {
		return $this->connectionPool->getConnections();
	}

	/**
	 * @return ConnectionPool
	 */
	public function getConnectionPool(): ConnectionPool {
		return $this->connectionPool;
	}

	/**
	 * Endpoint: search
	 * @param array $params
	 * @param bool $obj
	 * @return array|Response
	 */
	public function search(array $params = [], $obj = false) {
		$endpoint = new Endpoints\Search($params);
		$response = $this->request($endpoint);
		if ($obj === true) {
			return $response;
		}

		return $response->getResponse();
	}

	/**
	 * Endpoint: insert
	 * @param array $params
	 * @return array
	 */
	public function insert(array $params = []) {
		$endpoint = new Endpoints\Insert($params);
		$response = $this->request($endpoint);

		return $response->getResponse();
	}

	/**
	 * Endpoint: replace
	 * @param array $params
	 * @return mixed
	 */
	public function replace(array $params = []) {
		$endpoint = new Endpoints\Replace($params);
		$response = $this->request($endpoint);

		return $response->getResponse();
	}

	/**
	 * Endpoint: update
	 * @param array $params
	 * @return array
	 */
	public function update(array $params = []) {
		$endpoint = new Endpoints\Update($params);
		$response = $this->request($endpoint);

		return $response->getResponse();
	}


	/**
	 * Endpoint: sql
	 * @param mixed $params
	 * @return array
	 *
	 * $params can be either two parameters (string $query, bool $rawMode = false),
	 * or a single parameter with the following structure (array [ 'mode' => $mode, 'body' => ['query' => $query] ])
	 * The second variant is currently supported to provide compatibility with the older versions of the client
	 */
	public function sql(...$params) {
		if (is_string($params[0])) {
			$params = [
				'body' => [
					'query' => $params[0],
				],
				'mode' => !empty($params[1]) && is_bool($params[1]) ? 'raw' : '',
			];
		} else {
			$params = $params[0];
		}
		$endpoint = new Endpoints\Sql($params);
		if (isset($params['mode'])) {
			$endpoint->setMode($params['mode']);
			$response = $this->request($endpoint, ['responseClass' => SqlToArray::class]);
		} else {
			$response = $this->request($endpoint);
		}
		return $response->getResponse();
	}

	/**
	 * Endpoint: delete
	 * @param array $params
	 * @return array
	 */
	public function delete(array $params = []) {
		$endpoint = new Endpoints\Delete($params);
		$response = $this->request($endpoint);

		return $response->getResponse();
	}

	/**
	 * Endpoint: pq
	 */
	public function pq(): Pq {
		return new Pq($this);
	}

	/**
	 * Endpoint: indices
	 */
	public function indices(): Indices {
		return new Indices($this);
	}

	/**
	 * Endpoint: nodes
	 */
	public function nodes(): Nodes {
		return new Nodes($this);
	}

	public function cluster(): Cluster {
		return new Cluster($this);
	}

	/**
	 * Return Index object
	 *
	 * @param string|null $name Name of index
	 *
	 * @return \Manticoresearch\Index
	 */
	public function index(string $name = null): Index {
		return new Index($this, $name);
	}

	/**
	 * Endpoint: bulk
	 * @param array $params
	 * @return array
	 */
	public function bulk(array $params = []) {
		$endpoint = new Endpoints\Bulk($params);
		$response = $this->request($endpoint);

		return $response->getResponse();
	}

	/**
	 * Endpoint: suggest
	 * @param array $params
	 * @return array
	 */
	public function suggest(array $params = []) {
		$endpoint = new Endpoints\Suggest();
		$endpoint->setIndex($params['index']);
		$endpoint->setBody($params['body']);
		$response = $this->request(
			$endpoint,
			[
				'responseClass' => SqlToArray::class,
				'responseClassParams' => ['customMapping' => true],
			]
		);
		return $response->getResponse();
	}

	public function keywords(array $params = []) {
		$endpoint = new Endpoints\Keywords();
		$endpoint->setIndex($params['index']);
		$endpoint->setBody($params['body']);
		$response = $this->request($endpoint, ['responseClass' => SqlToArray::class]);
		return $response->getResponse();
	}

	public function explainQuery(array $params = []) {
		$endpoint = new Endpoints\ExplainQuery();
		$endpoint->setIndex($params['index']);
		$endpoint->setBody($params['body']);
		$response = $this->request(
			$endpoint,
			[
				'responseClass' => SqlToArray::class,
				'responseClassParams' => ['customMapping' => true],
			]
		);
		return $response->getResponse();
	}


	/*
	 * @return Response
	 */
	public function request(Request $request, array $params = []): Response {
		try {
			$connection = $this->connectionPool->getConnection();
			$this->lastResponse = $connection->getTransportHandler($this->logger)->execute($request, $params);
		} catch (NoMoreNodesException $e) {
			$this->logger->error(
				'Manticore Search Request out of retries:', [
				'exception' => $e->getMessage(),
				'request' => $request->toArray(),
				]
			);

			$this->initConnections();
			throw $e;
		} catch (ConnectionException $e) {
			$curRetryCount = sizeof($this->connectionPool->retriesInfo);
			$exMsg = $e->getMessage();
			// We rely on the common error message format from Manticore here
			$exceptionReason = substr($exMsg, strrpos($exMsg, ':') + 1);
			$this->connectionPool->retriesInfo[$curRetryCount - 1]['reason'] = $exceptionReason;
			$this->logger->warning(
				'Manticore Search Request failed on attempt ' . $this->connectionPool->retriesAttempts . ':',
				[
					'exception' => $exMsg,
					'request' => $e->getRequest()->toArray(),
				]
			);

			if ($connection) {
				$connection->mark(false);
			}

			return $this->request($request, $params);
		}
		return $this->lastResponse;
	}

	/*
	 *
	 * @return Response
	 */
	public function getLastResponse(): Response {
		return $this->lastResponse;
	}
}
