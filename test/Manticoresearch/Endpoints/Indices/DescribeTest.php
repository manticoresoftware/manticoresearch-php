<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Describe;
use Manticoresearch\Exceptions\RuntimeException;

class DescribeTest  extends \PHPUnit\Framework\TestCase
{
    public function testDescribeIndex()
    {
        $params = ['host' => $_SERVER['MS_HOST'], 'port' => 9308];
        $client = new Client($params);
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
        $client->indices()->create($params);

        $response = $client->indices()->describe(['index' => 'products']);

        $this->assertEquals([
            'id' => [
                'Type' => 'bigint',
                'Properties' => ''
            ],
            'title' => [
                'Type' => 'field',
                'Properties' => 'indexed stored'
            ],
            'price' => [
                'Type' => 'float',
                'Properties' => ''
            ],

        ], $response);
    }

    public function testSetGetIndex()
    {
        $describe = new Describe();
        $describe->setIndex('testName');
        $this->assertEquals('testName', $describe->getIndex());
    }

    public function testSetBodyNoIndex()
    {
        $describe = new Describe();
        $this->expectExceptionMessage('Index name is missing.');
        $this->expectException(RuntimeException::class);
        $describe->setBody([]);
    }


}
