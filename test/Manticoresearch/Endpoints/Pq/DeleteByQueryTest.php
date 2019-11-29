<?php

namespace Manticoresearch\Test\Endpoints\Pq;


use Manticoresearch\Client;
use Manticoresearch\Exceptions\RuntimeException;

class DeleteByQueryTest extends \PHPUnit\Framework\TestCase
{
    public function testMissingIndexName()
    {
        $client = new Client();
        $params = [
            'body' => [
                'id' => [1,2]
                ]
        ];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Index name is missing.');
        $response = $client->pq()->deleteByQuery($params);
    }
}