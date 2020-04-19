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
}
