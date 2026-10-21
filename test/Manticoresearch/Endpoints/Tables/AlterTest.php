<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Tables;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Tables\Alter;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class AlterTest extends \PHPUnit\Framework\TestCase
{
	/** @var Client */
	private static $client;

	/** @var PopulateHelperTest */
	private static $helper;

	public function setUp() : void {
		parent::setUp();

		$helper = new PopulateHelperTest('testDummy');
		$helper->populateForKeywords();
		static::$client = $helper->getClient();
		static::$helper = $helper;
	}

	public function testTableNoOperation() {
		$params = [
			'table' => 'products',
			'body' => [
				'column' => [
					'name' => 'price',
				],

			],
		];
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Operation is missing.');
		static::$client->tables()->alter($params);
	}

	public function testTableDropColumn() {
		$params = [
		'table' => 'products',
		'body' => [
			'operation' => 'drop',
			'column' => [
				'name' => 'price',
			],

		],
		];
		$response = static::$client->tables()->alter($params);
		$this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);

		// check the column has been added using the Describe endpoint
		$response = static::$client->tables()->describe(['table' => 'products']);

		$expectedResponse = [
		'id' =>
			[
				'Type' => 'bigint',
				'Properties' => '',
			],
		'title' =>
			[
				'Type' => 'field',
				'Properties' => 'indexed stored',
			],
		];
		$this->assertEquals(array_keys($expectedResponse), array_keys($response));
	}

	public function testTableAddColumn() {
		$params = [
			'table' => 'products',
			'body' => [
				'operation' => 'add',
				'column' => [
					'name' => 'tag',
					'type' => 'string',
				],

			],
		];
		$response = static::$client->tables()->alter($params);
		$this->assertEquals(['total' => 0,'error' => '','warning' => ''], $response);

		// check the column has been added using the Describe endpoint
		$response = static::$client->tables()->describe(['table' => 'products']);

		$expectedResponse = [
			'id' =>
				[
					'Type' => 'bigint',
					'Properties' => '',
				],
			'title' =>
				[
					'Type' => 'field',
					'Properties' => 'indexed stored',
				],
			'price' =>
				[
					'Type' => 'float',
					'Properties' => '',
				],

			// this is the new column
			'tag' =>
				[
					'Type' => 'string',
					'Properties' => '',
				],
		];
		$this->assertEquals(array_keys($expectedResponse), array_keys($response));
	}

	public function testTableAddColumnWithSingleOption() {
		$params = [
			'table' => 'products',
			'body' => [
				'operation' => 'add',
				'column' => [
					'name' => 'content_single',
					'type' => 'text',
					'options' => 'indexed',
				],
			],
		];
		$response = static::$client->tables()->alter($params);
		$this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);

		$response = static::$client->tables()->describe(['table' => 'products']);
		$this->assertTrue(isset($response['content_single']), 'Column content_single is missing');

		$properties = (string)$response['content_single']['Properties'];
		$this->assertStringContainsString('indexed', $properties);
	}

	public function testTableAddColumnWithMultipleOptions() {
		$params = [
			'table' => 'products',
			'body' => [
				'operation' => 'add',
				'column' => [
					'name' => 'content_multiple',
					'type' => 'text',
					'options' => ['indexed', 'stored'],
				],
			],
		];
		$response = static::$client->tables()->alter($params);
		$this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);

		$response = static::$client->tables()->describe(['table' => 'products']);
		$this->assertTrue(isset($response['content_multiple']), 'Column content_multiple is missing');

		$properties = (string)$response['content_multiple']['Properties'];
		$this->assertStringContainsString('indexed', $properties);
		$this->assertStringContainsString('stored', $properties);
	}

	public function testSetGetTable() {
		$alter = new Alter();
		$alter->setTable('testName');
		$this->assertEquals('testName', $alter->getTable());
	}

	public function testSetBodyNoTable() {
		$alter = new Alter();
		$this->expectExceptionMessage('Table name is missing.');
		$this->expectException(RuntimeException::class);
		$alter->setBody([]);
	}
}
