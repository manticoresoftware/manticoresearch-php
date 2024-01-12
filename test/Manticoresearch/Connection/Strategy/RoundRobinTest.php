<?php
namespace Manticoresearch\Test\Connection\Strategy;

use Manticoresearch\Client;
use Mockery as mock;
use PHPUnit\Framework\TestCase;

class RoundRobinTest extends TestCase
{
    public function testSequenceGood()
    {
        $client = new Client(["connectionStrategy"  =>"RoundRobin"]);

        $client->setHosts([
            [
                'host' => $_SERVER['MS_HOST'],
                'port' => $_SERVER['MS_PORT'],
                'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT']
            ],
            [
                'host' => $_SERVER['MS_HOST'],
                'port' => 9309,
                'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT']
            ],
        ]);

        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame($_SERVER['MS_HOST'], $connection->getHost());
        $this->assertSame($_SERVER['MS_PORT'], $connection->getPort());

        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame($_SERVER['MS_HOST'], $connection->getHost());
        $this->assertSame(9309, $connection->getPort());

        $mConns = [];
        for ($i=0; $i<10; $i++) {
            $mConns[] = mock::mock(\Manticoresearch\Connection::class)
                ->shouldReceive('isAlive')->andReturn(true)
                ->shouldReceive('getHost')->andReturn($_SERVER['MS_HOST'])
                ->shouldReceive('getPort')->andReturn((int)($_SERVER['MS_PORT']))
                ->getMock();
        }
        $connectionPool = new \Manticoresearch\Connection\ConnectionPool(
            $mConns,
            new \Manticoresearch\Connection\Strategy\RoundRobin(),
            10
        );
        foreach (range(0, 9) as $i) {
            $c = $connectionPool->getConnection();
            $this->assertSame($mConns[$i], $c);
        }
    }
}
