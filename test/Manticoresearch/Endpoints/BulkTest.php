<?php

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;
use Manticoresearch\Exceptions\ResponseException;

class BulkTest extends \PHPUnit\Framework\TestCase
{
    private static $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $params = [
            'host' => $_SERVER['MS_HOST'],
            'port' => $_SERVER['MS_PORT'],
            'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT']
        ];

        static::$client = new Client($params);
        $params = [
            'index' => 'bulktest',
            'body' => [
                'columns' => [
                    'title' => [
                        'type' => 'text'
                    ],
                ],
                'silent' => true
            ]
        ];

        static::$client->indices()->create($params);
        static::$client->indices()->truncate(['index' => 'bulktest']);
    }

    public function testBulkInsertError()
    {
        $response = static::$client->bulk(['body' => [
            ['insert' => ['index' => 'bulktest', 'id' => 1, 'doc' => ['title' => 'test']]],
            ['insert' => ['index' => 'bulktest', 'id' => 2, 'doc' => ['title' => 'test']]],
            ['insert' => ['index' => 'bulktest', 'id' => 3, 'doc' => ['title' => 'test']]],
        ]]);
        $this->expectException(ResponseException::class);
        $response = static::$client->bulk(['body' => [
            ['insert' => ['index' => 'bulktest', 'id' => 1, 'doc' => ['title' => 'test']]],
            ['insert' => ['index' => 'bulktest', 'id' => 2, 'doc' => ['title' => 'test']]],
            ['insert' => ['index' => 'bulktest', 'id' => 3, 'doc' => ['title' => 'test']]],
        ]]);
    }

    public function testDelete()
    {
        $response = static::$client->search(['body' => ['index' => 'bulktest', 'query' => ['match_all' => '']]]);
        $response = static::$client->bulk(['body' => [
            ['insert' => ['index' => 'bulktest', 'id' => 4, 'doc' => ['title' => 'test']]],
            ['delete' => ['index' => 'bulktest', 'id' => 2]],
            ['delete' => ['index' => 'bulktest', 'id' => 3]],
        ]]);

        $this->assertEquals(1, count($response['items']));
        $responseKeys = array_keys($response['items'][0]);
        $this->assertEquals(1, count($responseKeys));
        $this->assertEquals('bulk', array_shift($responseKeys));
        $response = static::$client->search(['body' => ['index' => 'bulktest', 'query' => ['match_all' => '']]]);
        $this->assertEquals(2, $response['hits']['total']);
    }

    public function testSetBodyAsString()
    {
        $bulk = new \Manticoresearch\Endpoints\Bulk();
        $bulk->setBody('some string');
        $this->assertEquals('some string', $bulk->getBody());
    }
}
