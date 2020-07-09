<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Truncate;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class TruncateTest extends \PHPUnit\Framework\TestCase
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

    public function testIndexTruncate()
    {
        $response = self::$client->indices()->truncate(['index' => 'products']);

        $this->assertEquals(['total'=>0,'error'=>'','warning'=>''], $response);
    }

    public function testSetGetIndex()
    {
        $describe = new Truncate();
        $describe->setIndex('testName');
        $this->assertEquals('testName', $describe->getIndex());
    }

    public function testSetBodyNoIndex()
    {
        $describe = new Truncate();
        $this->expectExceptionMessage('Index name is missing.');
        $this->expectException(RuntimeException::class);
        $describe->setBody([]);
    }
}
