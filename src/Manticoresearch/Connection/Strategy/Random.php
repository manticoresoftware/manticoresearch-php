<?php

namespace Manticoresearch\Connection\Strategy;

class Random implements SelectorInterface
{
    public function getConnection(array $connections)
    {
        shuffle($connections);
        foreach($connections  as $connection) {
            if($connection->isAlive()) {
                return $connection;
            }
        }
        //return $connections[array_rand($connections)];
    }
}