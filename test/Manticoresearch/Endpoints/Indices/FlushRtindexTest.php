<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\FlushRtindex;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class FlushRtindexTest extends \PHPUnit\Framework\TestCase
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
        static::$client = $helper->getClient();
        static::$helper = $helper;
    }

    public function testFlushRTIndex()
    {
        $response = static::$client->indices()->flushrtindex(['index' => 'products']);

        $this->assertEquals(['total'=>0,'error'=>'','warning'=>''], $response);
    }

    public function testSetGetIndex()
    {
        $describe = new FlushRtindex();
        $describe->setIndex('testName');
        $this->assertEquals('testName', $describe->getIndex());
    }

    public function testSetBodyNoIndex()
    {
        $describe = new FlushRtindex();
        $this->expectExceptionMessage('Index name is missing.');
        $this->expectException(RuntimeException::class);
        $describe->setBody([]);
    }
}
