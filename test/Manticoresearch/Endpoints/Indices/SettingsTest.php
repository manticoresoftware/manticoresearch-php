<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Describe;
use Manticoresearch\Endpoints\Indices\Optimize;
use Manticoresearch\Endpoints\Indices\Settings;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class SettingsTest  extends \PHPUnit\Framework\TestCase
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

    public function testSettings()
    {
        $response = self::$client->indices()->settings(['index' => 'products']);

        $this->assertEquals([], $response);
    }

    public function testSetGetIndex()
    {
        $describe = new Settings();
        $describe->setIndex('testName');
        $this->assertEquals('testName', $describe->getIndex());
    }

    public function testSetBodyNoIndex()
    {
        $describe = new Settings();
        $this->expectExceptionMessage('Index name is missing.');
        $this->expectException(RuntimeException::class);
        $describe->setBody([]);
    }


}
