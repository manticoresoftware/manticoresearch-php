<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Alter;
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

		$helper = new PopulateHelperTest();
		$helper->populateForKeywords();
		self::$client = $helper->getClient();
		self::$helper = $helper;
	}

	public function testIndexNoOperation() {
		$params = [
			'index' => 'products',
			'body' => [
				'column' => [
					'name' => 'price',
				],

			],
		];
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Operation is missing.');
		self::$client->indices()->alter($params);
	}

	public function testIndexDropColumn() {
		$params = [
			'index' => 'products',
			'body' => [
				'operation' => 'drop',
				'column' => [
					'name' => 'price',
				],

			],
		];
		$response = self::$client->indices()->alter($params);
		$this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);

		// check the column has been added using the Describe endpoint
		$response = self::$client->indices()->describe(['index' => 'products']);

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

	public function testIndexAddColumn() {
		$params = [
			'index' => 'products',
			'body' => [
				'operation' => 'add',
				'column' => [
					'name' => 'tag',
					'type' => 'string',
				],

			],
		];
		$response = self::$client->indices()->alter($params);
		$this->assertEquals(['total' => 0,'error' => '','warning' => ''], $response);

		// check the column has been added using the Describe endpoint
		$response = self::$client->indices()->describe(['index' => 'products']);

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

	public function testSetGetIndex() {
		$alter = new Alter();
		$alter->setIndex('testName');
		$this->assertEquals('testName', $alter->getIndex());
	}

	public function testSetBodyNoIndex() {
		$alter = new Alter();
		$this->expectExceptionMessage('Index name is missing.');
		$this->expectException(RuntimeException::class);
		$alter->setBody([]);
	}
}
