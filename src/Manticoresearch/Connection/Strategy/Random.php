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
     * @return Connection
     */
    public function getConnection(array $connections): Connection
    {
        shuffle($connections);
        return $connections[0];
    }
}
