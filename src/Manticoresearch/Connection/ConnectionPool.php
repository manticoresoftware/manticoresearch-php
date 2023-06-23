<?php

namespace Manticoresearch\Connection;

use Manticoresearch\Connection;
use Manticoresearch\Connection\Strategy\SelectorInterface;
use Manticoresearch\Exceptions\ConnectionException;
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

    public $retries_attempts =0;

    public function __construct(array $connections, SelectorInterface $strategy, int $retries)
    {
        $this->connections = $connections;
        $this->strategy = $strategy;
        $this->retries = $retries;
    }

    /**
     * @return array
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * @param array $connections
     */
    public function setConnections(array $connections)
    {
        $this->connections = $connections;
    }
    public function getConnection(): Connection
    {
        $this->retries_attempts++;
        $connection =   $this->strategy->getConnection($this->connections);
        if ($connection->isAlive()) {
            return $connection;
        }
        if ($this->retries_attempts < $this->retries) {
            return $connection;
        }
        $exMsg = 'After %d retr%s to %d node%s, connection has failed. No more retries left.';
        $connCount = count($this->connections);
        throw new NoMoreNodesException(
            sprintf($exMsg, $this->retries, $this->retries > 1 ? 'ies' : 'y', $connCount, $connCount > 1 ? 's' : '' )
        );
    }

    public function hasConnections(): bool
    {
        return $this->retries_attempts < $this->retries;
    }

    /**
     * @return SelectorInterface
     */
    public function getStrategy(): SelectorInterface
    {
        return $this->strategy;
    }

    /**
     * @param SelectorInterface $strategy
     */
    public function setStrategy(SelectorInterface $strategy)
    {
        $this->strategy = $strategy;
    }
}
