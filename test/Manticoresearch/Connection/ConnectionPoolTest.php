<?php

namespace Manticoresearch\Test\Connection;

use Manticoresearch\Client;
use Manticoresearch\Connection;
use PHPUnit\Framework\TestCase;

class ConnectionPoolTest extends TestCase
{
    /** @var Connection\ConnectionPool */
    private $connectionPool;

    public function setUp()
    {
        parent::setUp();
        $this->connectionPool = new Connection\ConnectionPool([], new Connection\Strategy\StaticRoundRobin(), 4);
    }

    public function testSetGetStrategy()
    {
        // change the connection pool strategy
        $this->connectionPool->setStrategy(new Connection\Strategy\RoundRobin());
        $this->assertEquals('Manticoresearch\Connection\Strategy\RoundRobin',
            get_class($this->connectionPool->getStrategy()));
    }

    public function testHasConnection()
    {
        $this->assertTrue($this->connectionPool->hasConnections());

        $this->connectionPool = new Connection\ConnectionPool([], new Connection\Strategy\StaticRoundRobin(), -1);
        $this->assertFalse($this->connectionPool->hasConnections());

    }

    public function testSetConnections()
    {
        $client = new Client();
        $this->assertCount(1, $client->getConnections());
        $connections = $client->getConnections();
        $this->connectionPool->setConnections($connections);
        $this->assertEquals($connections, $this->connectionPool->getConnections());
    }

    public function testGetConnection()
    {
        $client = new Client();
        $this->assertCount(1, $client->getConnections());
        $connections = $client->getConnections();
        $this->connectionPool->setConnections($connections);

        $connection = $this->connectionPool->getConnection();
        $this->assertEquals($connections[0], $connection);
    }

    public function testGetConnectionNotAlive()
    {
        $client = new Client();
        $this->assertCount(1, $client->getConnections());
        $connections = $client->getConnections();
        $connection = $connections[0];
        $connection->mark(false);
        $this->connectionPool->setConnections([$connection]);

        $connection = $this->connectionPool->getConnection();
        $this->assertEquals($connections[0], $connection);
    }


}
