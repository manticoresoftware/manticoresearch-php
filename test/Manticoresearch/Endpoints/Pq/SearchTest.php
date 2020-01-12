<?php

namespace Manticoresearch\Test\Endpoints\Pq;


use Manticoresearch\Client;
use Manticoresearch\Exceptions\RuntimeException;

class SearchTest extends \PHPUnit\Framework\TestCase
{
    public function testMissingIndexName()
    {
        $client = new Client();
        $params = [
            'body' => [
                'query' => [
                    'percolate' => [
                        'document' => [
                            'subject'=>'test',
                            'content' => 'some content',
                            'catid' =>5
                        ]
                    ]
                ]
            ]
        ];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Index name is missing.');
        $response = $client->pq()->doc($params);
    }
}