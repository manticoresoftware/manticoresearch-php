<?php

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;

class HelpersTest extends \PHPUnit\Framework\TestCase
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
            'index' => 'products',
            'body' => [
                'columns' => [
                    'title' => [
                        'type' => 'text',
                        'options' => ['indexed', 'stored']
                    ],
                    'price' => [
                        'type' => 'float'
                    ]
                ],
                'settings' => [
                    'rt_mem_limit' => '256M',
                    'min_infix_len' => '3'
                ],
                'silent' => true
            ]
        ];
        static::$client->indices()->create($params);
        static::$client->replace([
            'body' => [
                'index' => 'products',
                'id' => 100,
                'doc' => [
                    'title' => 'this product is not broken',
                    'price' => 2.99
                ]
            ]
        ]);
    }

    public function testKeywords()
    {
        $params = [
            'index' => 'products',
            'body' => [
                'query' => 'product',
                'options' => [
                    'stats' => 1,
                    'fold_lemmas' => 1
                ]
            ]
        ];
        $response = static::$client->keywords($params);
        $this->assertSame('product', $response['0']['normalized']);
    }

    public function testSuggest()
    {
        $params = [
            'index' => 'products',
            'body' => [
                'query' => 'brokn',
                'options' => [
                    'limit' => 5
                ]
            ]
        ];
        $response = static::$client->suggest($params);
        $this->assertSame('broken', array_keys($response)[0]);
    }
}
