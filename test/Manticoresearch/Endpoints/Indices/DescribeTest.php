<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Describe;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class DescribeTest  extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    private static $client;

    /** @var PopulateHelperTest */
    private static $helper;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $helper = new PopulateHelperTest();
        $helper->populateForKeywords();
        self::$client = $helper->getClient();
        self::$helper = $helper;
    }

    public function testDescribeIndex()
    {
        $response = self::$client->indices()->describe(['index' => 'products']);

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
