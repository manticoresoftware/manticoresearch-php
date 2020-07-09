<?php
namespace Manticoresearch\Test\Connection\Strategy;

use Manticoresearch\Client;
use Mockery as mock;
use PHPUnit\Framework\TestCase;

class StaticRoundRobinTest extends TestCase
{
    public function testTwoConnections()
    {
        $client = new Client(["connectionStrategy"  =>"StaticRoundRobin"]);

        $client->setHosts([
            [
                'host' => $_SERVER['MS_HOST'],
                'port' => $_SERVER['MS_PORT'],
                'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT']
            ],
            [
                'host' => $_SERVER['MS_HOST'],
                'port' => $_SERVER['MS_PORT'],
                'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT']
            ],
        ]);

        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame($_SERVER['MS_HOST'], $connection->getHost());
        $this->assertSame($_SERVER['MS_PORT'], $connection->getPort());

        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame($_SERVER['MS_HOST'], $connection->getHost());
    }

    public function testBadFirst()
    {

        $client = new Client(["connectionStrategy"  =>"StaticRoundRobin"]);

        $client->setHosts([
            [
                'host' => $_SERVER['MS_HOST'],
                'port' => 9309
            ],
            [
                'host' => $_SERVER['MS_HOST'],
                'port' => $_SERVER['MS_PORT']
            ],

        ]);

        $params = [
            'index' => 'testrt',
            'body' => [
                'columns' => [
                    'title' => [
                        'type' => 'text',
                        'options' => ['indexed', 'stored']
                    ]
                ],
                'silent' => true
            ]
        ];
        $response = $client->indices()->create($params);
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
        $this->assertSame($_SERVER['MS_PORT'], $client->getConnectionPool()->getConnection()->getPort());
    }
    public function testSequence()
    {

        $mConns = [];
        for ($i=0; $i<10; $i++) {
            $mConns[] = mock::mock(\Manticoresearch\Connection::class)
                ->shouldReceive('isAlive')->andReturn(true)->getMock();
        }
        $connectionPool = new \Manticoresearch\Connection\ConnectionPool(
            $mConns,
            new \Manticoresearch\Connection\Strategy\StaticRoundRobin(),
            10
        );
        foreach (range(0, 9) as $i) {
            $c = $connectionPool->getConnection();
            $this->assertSame($mConns[0], $c);
        }
    }
}
