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
                'host' => 'manticoresearch-manticore',
                'port' => 9308
            ],
            [
                'host' => 'manticoresearch-manticore',
                'port' => 9308
            ],

        ]);

        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame('manticoresearch-manticore', $connection->getHost());
        $this->assertSame(9308, $connection->getPort());

        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame('manticoresearch-manticore', $connection->getHost());

    }

    public function testBadFirst()
    {

        $client = new Client(["connectionStrategy"  =>"StaticRoundRobin"]);

        $client->setHosts([
            [
                'host' => 'manticoresearch-manticore',
                'port' => 9309
            ],
            [
                'host' => 'manticoresearch-manticore',
                'port' => 9308
            ],

        ]);
        $params = [
            'body' => [
                'index' => 'testrt',
                'query' => [
                    'match_phrase' => [
                        'title' => 'find me',
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