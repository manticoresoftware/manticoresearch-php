<?php


namespace Manticoresearch\Test;

use Manticoresearch\Connection;
use Manticoresearch\Transport;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class TransportTest extends TestCase
{
    public function testBadStaticTransportCreate()
    {
        $connection = new Connection([]);
        $this->expectException('Exception');
        $this->expectExceptionMessage('Bad transport');
        $transport = Transport::create('badtransport', $connection, new NullLogger());
    }

    public function testSetUpURI()
    {
        $transport = new Transport();
        $class = new \ReflectionClass('Manticoresearch\Transport');
        $method = $class->getMethod('setupURI');
        $method->setAccessible(true);

        $url = $method->invokeArgs($transport, ['/search', ['a' => 1, 'b' => false]]);
        $this->assertEquals('/search?a=1&b=false', $url);
    }
}
