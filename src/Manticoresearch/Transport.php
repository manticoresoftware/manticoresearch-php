<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Transport
 * @package Manticoresearch
 */
class Transport
{
	/**
	 * @var Connection
	 */
	protected $connection;

	/**
	 * @var LoggerInterface|NullLogger
	 */
	protected $logger;

	/**
	 * Transport constructor.
	 * @param Connection|null $connection
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(?Connection $connection = null, ?LoggerInterface $logger = null) {
		if ($connection) {
			$this->connection = $connection;
		}
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * @return Connection|null
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * @param Connection $connection
	 * @return Transport
	 */
	public function setConnection(Connection $connection): Transport {
		$this->connection = $connection;
		return $this;
	}

	/**
	 * @param string $transport
	 * @param Connection $connection
	 * @param LoggerInterface $logger
	 * @param array<mixed> $clientParams
	 * @return mixed
	 * @throws \Exception
	 */
	public static function create(
		string $transport,
		Connection $connection,
		LoggerInterface $logger,
		array $clientParams = []
	) {
		if (is_string($transport)) {
			$className = "Manticoresearch\\Transport\\$transport";
			if (class_exists($className)) {
				$transport = new $className($connection, $logger, ...$clientParams);
			}
		}
		if (!($transport instanceof self)) {
			throw new \Exception('Bad transport');
		}

		$transport->setConnection($connection);
		return $transport;
	}

	/**
	 * @param string $uri
	 * @param array $query
	 * @return string
	 */
	protected function setupURI(string $uri, $query = []): string {
		if (!empty($query)) {
			foreach ($query as $k => $v) {
				if (!is_bool($v)) {
					continue;
				}

				$query[$k] = $v ? 'true' : 'false';
			}
			$uri .= '?' . http_build_query($query);
		}
		return $uri;
	}
}
