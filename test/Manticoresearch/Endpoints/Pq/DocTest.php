<?php

namespace Manticoresearch\Test\Endpoints\Pq;


use Manticoresearch\Client;
use Manticoresearch\Exceptions\RuntimeException;

class DocTest extends \PHPUnit\Framework\TestCase
{
    public function testMissingIndexName()
    {
        $client = new Client();
        $params = [

            'body' => [
                'query' => ['match'=>['subject'=>'test']],
                'tags' => ['test1','test2']
            ]
        ];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Index name is missing.');
        $response = $client->pq()->doc($params);
    }
}