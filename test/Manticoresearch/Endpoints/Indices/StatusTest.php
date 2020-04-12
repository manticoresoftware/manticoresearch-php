<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Describe;
use Manticoresearch\Endpoints\Indices\Optimize;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class OptimizeTest  extends \PHPUnit\Framework\TestCase
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
        $response = self::$client->indices()->optimize(['index' => 'products']);

        $this->assertEquals([
            'total' => 0,
            'error' => '',
            'warning' => ''
        ], $response);
    }

    public function testSetGetIndex()
    {
        $describe = new Optimize();
        $describe->setIndex('testName');
        $this->assertEquals('testName', $describe->getIndex());
    }

    public function testSetBodyNoIndex()
    {
        $describe = new Optimize();
        $this->expectExceptionMessage('Index name is missing.');
        $this->expectException(RuntimeException::class);
        $describe->setBody([]);
    }


}
