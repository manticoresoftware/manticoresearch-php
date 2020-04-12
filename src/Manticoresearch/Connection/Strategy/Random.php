<?php

namespace Manticoresearch\Connection\Strategy;

use Manticoresearch\Connection;

/**
 * Class Random
 * @package Manticoresearch\Connection\Strategy
 */
class Random implements SelectorInterface
{
    /**
     * @param array $connections
     * @return mixed
     */
    public function getConnection(array $connections):Connection
    {
        shuffle($connections);
        foreach ($connections as $connection) {
            return $connection;
        }
    }
}
