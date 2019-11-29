<?php


namespace Manticoresearch\Connection\Strategy;


class StaticRoundRobin implements SelectorInterface
{
    private $current = 0;

    public function getConnection(array $connections)
    {
        if($connections[$this->current]->isAlive()) {
            return $connections[$this->current];
        }

        $alives = array_filter($connections, function($connection){ return $connection->isAlive()??false;});
        $connection = $alives[$this->current % count($connections)];
        $this->current +=1;
        return $connection;
    }
}