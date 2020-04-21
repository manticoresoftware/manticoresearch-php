<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Drop;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class DropTest  extends \PHPUnit\Framework\TestCase
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

        $this->assertEquals( ['total'=>0,'error'=>'','warning'=>''],$response);

    }

    public function testSetGetIndex()
    {
        $describe = new Drop();
        $describe->setIndex('testName');
        $this->assertEquals('testName', $describe->getIndex());
    }

    public function testSetBodyNoIndex()
    {
        $describe = new Drop();
        $this->expectExceptionMessage('Missing index name in /indices/drop');
        $this->expectException(RuntimeException::class);
        $describe->setBody([]);
    }


}
