<?php

namespace Manticoresearch\Test\Endpoints;

class InsertTest extends \PHPUnit\Framework\TestCase
{
    public function testPath()
    {
        $insert = new \Manticoresearch\Endpoints\Insert();
        $this->assertEquals('/insert', $insert->getPath());
    }

    public function testGetMethod()
    {
        $insert = new \Manticoresearch\Endpoints\Insert();
        $this->assertEquals('POST', $insert->getMethod());
    }

    public function testInsert()
    {
        $helper = new \Manticoresearch\Test\Helper\PopulateHelperTest();
        $helper->populateForKeywords();
        $client = $helper->getClient();

        // insert a product
        $doc = [
            'index' => 'products',
            'id' => 1001,
            'doc' => [
                'title' => 'Star Trek: Nemesis DVD',
                'price' => 6.99
            ]
        ];
        $response = $client->insert(['body' => $doc]);

        // assert inserted
        $this->assertEquals([
            '_index' => 'products',
            '_id' => 1001,
            'created' => true,
            'result' => 'created',
            'status' => 201,
        ], $response);

        // search for inserted product
        $helper->search('products', 'Star Trek DVD', 1);

        // reinsert, this should fail due to duplicate ID
        $this->expectException(\Manticoresearch\Exceptions\ResponseException::class);
        $this->expectExceptionMessage('{"type":"duplicate id \'1001\'","index":"products"}');
        $response = $client->insert(['body' => $doc]);
    }
}
