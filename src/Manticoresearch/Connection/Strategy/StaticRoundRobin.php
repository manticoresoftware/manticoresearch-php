<?php


namespace Manticoresearch\Connection\Strategy;


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
    public function getConnection(array $connections)
    {
        if ($connections[$this->current]->isAlive()) {
            return $connections[$this->current];
        }

        $alives = array_filter($connections, function ($connection) {
            return $connection->isAlive() ?? false;
        });
        $this->current += 1;

        return $alives[$this->current % count($connections)];
    }
}