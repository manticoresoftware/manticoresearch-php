<?php

use Manticoresearch\Client;
use PHPUnit\Framework\TestCase;
use Mockery as mock;

class StaticRoundRobinTest extends TestCase
{
    public function testTwoConnections()
    {
        $client = new Client(["connectionStrategy"  =>"StaticRoundRobin"]);

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

    }

    public function testBadFirst()
    {

        $client = new Client(["connectionStrategy"  =>"StaticRoundRobin"]);

        $client->setHosts([
            [
                'host' => '127.0.0.1',
                'port' => '6381'
            ],
            [
                'host' => '127.0.0.1',
                'port' => '6380'
            ],

        ]);
        $params = [
            'body' => [
                'index' => 'movies',
                'query' => [
                    'match_phrase' => [
                        'movie_title' => 'star trek nemesis',
                    ]
                ]
            ]
        ];

        $client->search($params);
        $this->assertSame('6380', $client->getConnectionPool()->getConnection()->getPort());
    }
    public function testSequence()
    {

        $mConns = [];
        for($i=0;$i<10;$i++) {
            $mConns[] = mock::mock(\Manticoresearch\Connection::class)->shouldReceive('isAlive')->andReturn(true)->getMock();
        }
        $connectionPool = new \Manticoresearch\Connection\ConnectionPool($mConns, new \Manticoresearch\Connection\Strategy\StaticRoundRobin());
        foreach(range(0,9) as $i) {
            $c = $connectionPool->getConnection();
            $this->assertSame($mConns[0], $c);
        }
    }
}