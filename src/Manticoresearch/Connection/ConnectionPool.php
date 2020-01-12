<?php

namespace Manticoresearch\Connection;

use Manticoresearch\Connection;
use Manticoresearch\Connection\Strategy\SelectorInterface;

/**
 * Class ConnectionPool
 * @package Manticoresearch\Connection
 */
class ConnectionPool
{
    /**
     * @var array
     */
    protected $_connections;
    /**
     * @var SelectorInterface
     */
    protected $_strategy;

    public function __construct(array $connections, SelectorInterface $strategy)
    {
        $this->_connections = $connections;
        $this->_strategy = $strategy;
    }

    /**
     * @return array
     */
    public function getConnections(): array
    {
        return $this->_connections;
    }

    /**
     * @param array $connections
     */
    public function setConnections(array $connections)
    {
        $this->_connections = $connections;
    }

    public function getConnection(): Connection
    {

        $connection = $this->_strategy->getConnection($this->_connections);
        return $connection;
    }

    public function hasConnections(): bool
    {
        foreach ($this->_connections as $connection) {
            if ($connection->isAlive()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return SelectorInterface
     */
    public function getStrategy(): SelectorInterface
    {
        return $this->_strategy;
    }

    /**
     * @param SelectorInterface $strategy
     */
    public function setStrategy(SelectorInterface $strategy)
    {
        $this->_strategy = $strategy;
    }


}