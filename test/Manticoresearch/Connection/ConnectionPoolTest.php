<?php

namespace Manticoresearch\Test\Connection;

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
        $this->assertEquals('Manticoresearch\Connection\Strategy\RoundRobin', get_class($this->connectionPool->getStrategy()));
    }


}
