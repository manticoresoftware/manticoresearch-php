<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Tables;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Tables\Status;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class StatusTest extends \PHPUnit\Framework\TestCase
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

	public function testTableStatus() {
		$response = static::$client->tables()->status(['table' => 'products']);
		$this->assertArrayHasKey('disk_bytes', $response);
	}

	public function testSetGetTable() {
		$describe = new Status();
		$describe->setTable('testName');
		$this->assertEquals('testName', $describe->getTable());
	}

	public function testSetBodyNoTable() {
		$describe = new Status();
		$this->expectExceptionMessage('Table name is missing.');
		$this->expectException(RuntimeException::class);
		$describe->setBody([]);
	}
}
