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
        $this->connectionPool = new Connection\ConnectionPool([]);
    }

    public function testSetGetStrategy()
    {
        $this->connectionPool->setStrategy('StaticRoundRobin');
        $this->assertEquals('StaticRoundRobin', $this->connectionPool->getStrategy());
    }


}
