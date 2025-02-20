<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Tables;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Tables\FlushRamchunk;
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

		$helper = new PopulateHelperTest('testDummy');
		$helper->populateForKeywords();
		static::$client = $helper->getClient();
		static::$helper = $helper;
	}

	public function testFlushRamchunkTable() {
		$response = static::$client->tables()->flushramchunk(['table' => 'products']);

		$this->assertEquals(['total' => 0,'error' => '','warning' => ''], $response);
	}

	public function testSetGetTable() {
		$describe = new FlushRamchunk();
		$describe->setTable('testName');
		$this->assertEquals('testName', $describe->getTable());
	}

	public function testSetBodyNoTable() {
		$describe = new FlushRamchunk();
		$this->expectExceptionMessage('Table name is missing.');
		$this->expectException(RuntimeException::class);
		$describe->setBody([]);
	}
}
