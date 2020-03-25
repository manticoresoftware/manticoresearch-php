<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;

class CreateTest  extends \PHPUnit\Framework\TestCase
{
    public function testCreateTableWithOptions()
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
                'options' => [
                    'rt_mem_limit' => '256M',
                    'min_infix_len' => '3'
                ]
            ]
        ];
        $response = $client->indices()->create($params);
        $this->assertSame( ['total'=>0,'error'=>'','warning'=>''],$response);
        $params = [
            'index'=>'products'
        ];
        $response = $client->indices()->drop($params);
        $this->assertSame( ['total'=>0,'error'=>'','warning'=>''],$response);
    }
}