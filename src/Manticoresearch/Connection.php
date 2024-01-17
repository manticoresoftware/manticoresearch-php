<?php


namespace Manticoresearch;

use Manticoresearch\Exceptions\RuntimeException;
use Psr\Log\LoggerInterface;

/**
 * Class Connection
 * @package Manticoresearch
 */
class Connection
{
	/**
	 * @var array
	 */
	protected $config;
	/**
	 * @var bool
	 */
	protected $alive = true;
/*
 * $params['transport']  = transport class name
 * $params['host']       = hostname
 * $params['path']       = path
 * $params['port']       = port number
 * $params['timeout']    = connection timeout
 * $params['connect_timeout'] = connection connect timeout
 * $params['proxy']       = proxy host:port string
 * $params['username']  = username for http auth
 * $params['password']  = password for http auth
 * $params['headers']   = array of custom headers
 * $params['curl']      = array of pairs of curl option=>value
 * $params['persistent'] = bool if connection is persistent
 */
	/**
	 * Connection constructor.
	 * @param array $params
	 */
	public function __construct(array $params) {
		$this->config = [
			'transport' => 'Http',
			'host' => '127.0.0.1',
			'scheme' => 'http',
			'path' => '',
			'port' => '9308',
			'timeout' => 300,
			'connect_timeout' => 0,
			'proxy' => null,
			'username' => null,
			'password' => null,
			'headers' => [],
			'curl' => [],
			'persistent' => true,
		];
		$this->config = array_merge($this->config, $params);
		$this->alive = true;
	}

	/**
	 * @param string $host
	 * @return $this
	 */
	public function setHost($host): self {
		$this->config['host'] = $host;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHost() {
		return $this->config['host'];
	}

	/**
	 * @param string $path
	 * @return $this
	 */
	public function setPath(string $path): self {
		$this->config['path'] = $path;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPath() {
		return $this->config['path'];
	}

	/**
	 * @param string|integer $port
	 * @return $this
	 */
	public function setPort($port): self {
		$this->config['port'] = (int)$port;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPort() {
		return $this->config['port'];
	}

	/**
	 * @param integer $timeout
	 * @return $this
	 */
	public function setTimeout($timeout): self {
		$this->config['timeout'] = (int)$timeout;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHeaders() {
		return $this->config['headers'];
	}

	/**
	 * @param array $headers
	 * @return $this
	 */
	public function setheaders($headers): self {
		$this->config['headers'] = $headers;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTimeout() {
		return $this->config['timeout'];
	}

	/**
	 * @param integer $connect_timeout
	 * @return $this
	 */
	public function setConnectTimeout($connectTimeout): self {
		$this->config['connect_timeout'] = (int)$connectTimeout;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getConnectTimeout() {
		return $this->config['connect_timeout'];
	}

	/**
	 * @param Transport $transport
	 * @return $this
	 */
	public function setTransport($transport): self {
		$this->config['transport'] = $transport;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTransport() {
		return $this->config['transport'];
	}

	/**
	 * @param LoggerInterface $logger
	 * @return mixed
	 * @throws \Exception
	 */
	public function getTransportHandler(LoggerInterface $logger) {
		return Transport::create($this->getTransport(), $this, $logger);
	}

	/**
	 * @param array $config
	 * @return $this
	 */
	public function setConfig($config): self {
		foreach ($config as $ckey => $cvalue) {
			$this->config[$ckey] = $cvalue;
		}
		return $this;
	}

	/**
	 * @param string|null $key
	 * @return mixed|null
	 *
	 */
	public function getConfig($key = null) {
		if ($key === null) {
			return $this->config;
		}
		return $this->config[$key] ?? null;
	}

	/**
	 * @param array|Connection $params|self
	 * @return Connection
	 */
	public static function create($params) {
		if (is_array($params)) {
			return new self($params);
		}
		if ($params instanceof self) {
			return $params;
		}
		throw new RuntimeException('connection must receive array of parameters or self');
	}

	/**
	 * @return bool
	 */
	public function isAlive(): bool {
		return $this->alive;
	}

	/**
	 * @param bool $state
	 */
	public function mark(bool $state) {
		$this->alive = $state;
	}
}
