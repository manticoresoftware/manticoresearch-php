<?php


namespace Manticoresearch\Test;


use Manticoresearch\Client;
use Manticoresearch\Connection\Strategy\Random;
use Manticoresearch\Connection\Strategy\RoundRobin;
use Manticoresearch\Exceptions\ConnectionException;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testEmptyConfig()
    {
        $client = new Client();
        $this->assertCount(1, $client->getConnections());
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
        $params = ['host' => '127.0.0.1', 'port' => 9307];
        $client = new Client($params);
        $this->expectException(ConnectionException::class);
        $client->search(['body'=>'']);
    }

    public function testDouble()
    {
        $params = ['connections'=>
            [
                [
                    'host' => '123.0.0.1',
                    'port' => '1234',
                    'timeout' => 5,
                    'connection_timeout' => 1,
                    'proxy' => '127.0.0.255',
                    'username' => 'test',
                    'password' => 'secret',
                    'headers' => [
                        'X-Forwarded-Host' => 'mydev.domain.com'
                    ],
                    'curl' => [
                        CURLOPT_FAILONERROR => true
                    ],
                    'persistent' => true
                ],
                [
                    'host' => '123.0.0.2',
                    'port' => '1235',
                    'timeout' => 5,
                    'transport' => 'Https',
                    'curl' =>[
                        CURLOPT_CAPATH => 'path/to/my/ca/folder',
                        CURLOPT_SSL_VERIFYPEER => true
                    ],
                    'connection_timeout' => 1,
                    'persistent' => true
                ],

            ]
        ];
        $client =  new Client($params);
        $this->expectException(ConnectionException::class);
        $client->search(['body'=>'']);
    }
}