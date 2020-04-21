<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Status;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class StatusTest  extends \PHPUnit\Framework\TestCase
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

    public function testIndexStatus()
    {
        $response = self::$client->indices()->status(['index' => 'products']);

        $this->assertEquals([
            'index_type',
            'indexed_documents',
            'indexed_bytes',
            'ram_bytes',
            'disk_bytes',
            'ram_chunk',
            'ram_chunks_count',
            'disk_chunks',
            'mem_limit',
            'ram_bytes_retired',
            'tid',
            'tid_saved',
            'query_time_1min',
            'query_time_5min',
            'query_time_15min',
            'query_time_total',
            'found_rows_1min',
            'found_rows_5min',
            'found_rows_15min',
            'found_rows_total',
        ], array_keys($response));
    }

    public function testSetGetIndex()
    {
        $describe = new Status();
        $describe->setIndex('testName');
        $this->assertEquals('testName', $describe->getIndex());
    }

    public function testSetBodyNoIndex()
    {
        $describe = new Status();
        $this->expectExceptionMessage('Index name is missing.');
        $this->expectException(RuntimeException::class);
        $describe->setBody([]);
    }


}
