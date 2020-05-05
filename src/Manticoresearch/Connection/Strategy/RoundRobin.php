<?php


namespace Manticoresearch\Connection\Strategy;


use Manticoresearch\Connection;

/**
 * Class RoundRobin
 * @package Manticoresearch\Connection\Strategy
 */
class RoundRobin implements SelectorInterface
{
    /**
     * @var int
     */
    private $current = 0;

    /**
     * @param array $connections
     * @return Connection
     */
    public function getConnection(array $connections) :Connection
    {
        $connection = $connections[$this->current % count($connections)];
        ++$this->current;
        return $connection;
    }
}
