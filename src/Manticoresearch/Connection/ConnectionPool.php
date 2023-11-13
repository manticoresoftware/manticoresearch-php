<?php

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

	public $retries_attempts = 0; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

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
		$this->retries_attempts++; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		$connection = $this->strategy->getConnection($this->connections);
		if ($connection->isAlive()) {
			return $connection;
		}
        // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		if ($this->retries_attempts < $this->retries) {
			return $connection;
		}
		$exMsg = 'After %d retr%s to %d node%s, connection has failed. No more retries left.';
		$connCount = sizeof($this->connections);
		throw new NoMoreNodesException(
			sprintf($exMsg, $this->retries, $this->retries > 1 ? 'ies' : 'y', $connCount, $connCount > 1 ? 's' : '')
		);
	}

	public function hasConnections(): bool {
        // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		return $this->retries_attempts < $this->retries;
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
