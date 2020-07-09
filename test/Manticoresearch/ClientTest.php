<?php


namespace Manticoresearch\Test;

use Manticoresearch\Client;
use Manticoresearch\Connection;
use Manticoresearch\Connection\Strategy\Random;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Test\Helper\PopulateHelperTest;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testEmptyConfig()
    {
        $client = new Client();
        $this->assertCount(1, $client->getConnections());
    }

    public function testObjectStrategy()
    {
        $client = new Client(["connectionStrategy"  => new Connection\Strategy\RoundRobin()]);
        $this->assertCount(1, $client->getConnections());
    }

    public function testClassnameStrategy()
    {
        $client = new Client(["connectionStrategy"  => 'Connection\Strategy\RoundRobin']);
        $this->assertCount(1, $client->getConnections());
    }

    public function testCluster()
    {
        $client = new Client();
        $this->assertInstanceOf('Manticoresearch\Cluster', $client->cluster());
    }

    public function testCreationWithConnection()
    {
        $params = [
            'host' => $_SERVER['MS_HOST'],
            'port' => $_SERVER['MS_PORT'],
            'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT']
        ];
        $connection = new Connection($params);
        $params = ['connections' => $connection];
        $client = new Client($params);
        $this->assertCount(1, $client->getConnections());
    }

    public function testCreationWithConnectionSingularArray()
    {
        $params = ['host' => $_SERVER['MS_HOST'], 'port' => $_SERVER['MS_PORT']];
        $connection = new Connection($params);
        $params = ['connections' => [$connection]];
        $client = new Client($params);
        $this->assertCount(1, $client->getConnections());
    }

    public function testStrategyConfig()
    {
        $params = ['connectionStrategy' => 'Random'];
        $client = Client::create($params); //new Client($params);
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

    public function testGetLastResponse()
    {
        $helper = new PopulateHelperTest();
        $helper->populateForKeywords();
        $client = $helper->getClient();

        $payload = [
            'body' => [
                'index' => 'products',
                'query' => [
                    'match' => ['*' => 'broken'],
                ],
            ]
        ];

        $result = $client->search($payload);
        $lastResponse = $client->getLastResponse()->getResponse();
        $this->assertEquals($result, $lastResponse);
    }
}
