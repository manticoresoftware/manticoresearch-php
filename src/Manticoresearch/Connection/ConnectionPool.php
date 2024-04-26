<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Connection;

use Manticoresearch\Connection;
use Manticoresearch\Connection\Strategy\SelectorInterface;
use Manticoresearch\Exceptions\NoMoreNodesException;

/**
 * Class ConnectionPool
 * @package Manticoresearch\Connection
 */
class ConnectionPool
{
	/**
	 * @var array
	 */
	protected $connections;

	/**
	 * @var SelectorInterface
	 */
	public $strategy;

	public $retries;

	public $retriesAttempts = 0;

	public $retriesInfo = [];

	public function __construct(array $connections, SelectorInterface $strategy, int $retries) {
		$this->connections = $connections;
		$this->strategy = $strategy;
		$this->retries = $retries;
	}

	/**
	 * @return array
	 */
	public function getConnections(): array {
		return $this->connections;
	}

	/**
	 * @param array $connections
	 */
	public function setConnections(array $connections) {
		$this->connections = $connections;
	}
	public function getConnection(): Connection {
		$this->retriesAttempts++;
		$connection = $this->strategy->getConnection($this->connections);
		if ($this->retriesAttempts <= $this->retries) {
			$this->retriesInfo[] = [
				'host' => $connection->getHost(),
				'port' => $connection->getPort(),
				'reason' => 'unknown',
			];
		}
		if ($connection->isAlive()) {
			return $connection;
		}
		if ($this->retriesAttempts < $this->retries) {
			return $connection;
		}
		$exMsg = 'After %d retr%s to %d node%s, connection has failed. No more retries left.';
		$exMsg .= "\nRetries made:\n";
		foreach ($this->retriesInfo as $i => $info) {
			$i++;
			$exMsg .= " $i. to {$info['host']}:{$info['port']}, failure reason:{$info['reason']}\n";
		}
		$connCount = sizeof($this->connections);
		throw new NoMoreNodesException(
			sprintf($exMsg, $this->retries, $this->retries > 1 ? 'ies' : 'y', $connCount, $connCount > 1 ? 's' : '')
		);
	}

	public function hasConnections(): bool {
		return $this->retriesAttempts < $this->retries;
	}

	/**
	 * @return SelectorInterface
	 */
	public function getStrategy(): SelectorInterface {
		return $this->strategy;
	}

	/**
	 * @param SelectorInterface $strategy
	 */
	public function setStrategy(SelectorInterface $strategy) {
		$this->strategy = $strategy;
	}
}
