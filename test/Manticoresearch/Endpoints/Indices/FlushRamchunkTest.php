<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\FlushRamchunk;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class FlushRamchunkTest extends \PHPUnit\Framework\TestCase
{
	/** @var Client */
	private static $client;

	/** @var PopulateHelperTest */
	private static $helper;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$helper = new PopulateHelperTest();
		$helper->populateForKeywords();
		static::$client = $helper->getClient();
		static::$helper = $helper;
	}

	public function testFlushRamchunkIndex() {
		$response = static::$client->indices()->flushramchunk(['index' => 'products']);

		$this->assertEquals(['total' => 0,'error' => '','warning' => ''], $response);
	}

	public function testSetGetIndex() {
		$describe = new FlushRamchunk();
		$describe->setIndex('testName');
		$this->assertEquals('testName', $describe->getIndex());
	}

	public function testSetBodyNoIndex() {
		$describe = new FlushRamchunk();
		$this->expectExceptionMessage('Index name is missing.');
		$this->expectException(RuntimeException::class);
		$describe->setBody([]);
	}
}
