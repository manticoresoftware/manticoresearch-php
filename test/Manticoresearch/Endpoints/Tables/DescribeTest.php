<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Tables;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Tables\Describe;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class DescribeTest extends \PHPUnit\Framework\TestCase
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

	public function testDescribeTable() {
		$response = static::$client->tables()->describe(['table' => 'products']);

		$this->assertEquals(
			array_keys(
				[
				'id' => [
				'Type' => 'bigint',
				'Properties' => '',
				],
				'title' => [
				'Type' => 'field',
				'Properties' => 'indexed stored',
				],
				'price' => [
				'Type' => 'float',
				'Properties' => '',
				],

				]
			), array_keys($response)
		);
	}

	public function testSetGetTable() {
		$describe = new Describe();
		$describe->setTable('testName');
		$this->assertEquals('testName', $describe->getTable());
	}

	public function testSetBodyNoTable() {
		$describe = new Describe();
		$this->expectExceptionMessage('Table name is missing.');
		$this->expectException(RuntimeException::class);
		$describe->setBody([]);
	}
}
