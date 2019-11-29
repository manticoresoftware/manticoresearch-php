<?php


namespace Manticoresearch\Test;


use Manticoresearch\Client;
use Manticoresearch\Connection\Strategy\Random;
use Manticoresearch\Exceptions\ConnectionException;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testEmptyConfig()
    {
        $client = new Client();
        $this->assertCount(1, $client->getConnections());
    }

    public function testHosts()
    {
        $client = new Client();

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
        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame('127.0.0.1', $connection->getHost());
        $this->assertSame('6381', $connection->getPort());

        $connection = $client->getConnectionPool()->getConnection();
        $this->assertSame('127.0.0.1', $connection->getHost());
        $this->assertSame('6380', $connection->getPort());

    }

    public function testStrategyConfig()
    {
        $params = ['connectionStrategy' => 'Random'];
        $client = new Client($params);
        $strategy = $client->getConnectionPool()->getStrategy();
        $this->assertInstanceOf(Random::class, $strategy);
    }

    public function testConnectionError()
    {
        $params = ['host'=>'127.0.0.1', 'port'=>9306];
        $client = new Client($params);
        $this->expectException(ConnectionException::class);
        $client->search(['body'=>'']);
    }
}