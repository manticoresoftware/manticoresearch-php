<?php


namespace Manticoresearch\Connection\Strategy;


use Manticoresearch\Connection;

/**
 * Class StaticRoundRobin
 * @package Manticoresearch\Connection\Strategy
 */
class StaticRoundRobin implements SelectorInterface
{
    /**
     * @var int
     */
    private $current = 0;

    /**
     * @param array $connections
     * @return mixed
     */
    public function getConnection(array $connections):Connection
    {
        echo $this->current."\n";
        if ($connections[$this->current % count($connections)]->isAlive()) {
            return $connections[$this->current];
        }
        ++$this->current;
        return $connections[$this->current % count($connections)];
    }
}