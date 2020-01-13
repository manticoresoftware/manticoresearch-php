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
                'host' => 'manticoresearch-manticore',
                'port' => 9308
            ],
            [
                'host' => 'manticoresearch-manticore',
                'port' => 9309
            ],

        ]);

        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame('manticoresearch-manticore', $connection->getHost());
        $this->assertSame(9308, $connection->getPort());

        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame('manticoresearch-manticore', $connection->getHost());
        $this->assertSame(9309, $connection->getPort());

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