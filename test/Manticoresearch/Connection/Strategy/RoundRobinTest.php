<?php

use Manticoresearch\Client;
use PHPUnit\Framework\TestCase;
use Mockery as mock;
class RoundRobinTest extends TestCase
{
    public function testSequenceGood()
    {
        $client = new Client(["connectionStrategy"  =>"RoundRobin"]);

        $client->setHosts([
            [
                'host' => '127.0.0.1',
                'port' => '6380'
            ],
            [
                'host' => '127.0.0.1',
                'port' => '6381'
            ],

        ]);

        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame('127.0.0.1', $connection->getHost());
        $this->assertSame('6380', $connection->getPort());

        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame('127.0.0.1', $connection->getHost());
        $this->assertSame('6381', $connection->getPort());

        $mConns = [];
        for($i=0;$i<10;$i++) {
            $mConns[] = mock::mock(\Manticoresearch\Connection::class)->shouldReceive('isAlive')->andReturn(true)->getMock();
        }
        $connectionPool = new \Manticoresearch\Connection\ConnectionPool($mConns, new \Manticoresearch\Connection\Strategy\RoundRobin());
        foreach(range(0,9) as $i) {
            $c = $connectionPool->getConnection();
            $this->assertSame($mConns[$i], $c);
        }

    }
}