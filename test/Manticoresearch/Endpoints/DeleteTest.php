<?php

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    private static $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $helper = new PopulateHelperTest();
        $helper->populateForKeywords();
        static::$client = $helper->getClient();
    }

    public function testPath()
    {
        $insert = new \Manticoresearch\Endpoints\Delete();
        $this->assertEquals('/json/delete', $insert->getPath());
    }

    public function testGetMethod()
    {
        $insert = new \Manticoresearch\Endpoints\Delete();
        $this->assertEquals('POST', $insert->getMethod());
    }

    public function testDelete()
    {
        $helper = new PopulateHelperTest();
        $helper->search('products', 'broken', 1);
        $doc = [
            'body' => [
                'index' => 'products',
                'id' => 100
            ]
        ];

        $response = static::$client->delete($doc);
        $helper->search('products', 'broken', 0);
    }
}
