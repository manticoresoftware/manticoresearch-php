<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;

//use Manticoresearch\Exceptions\ResponseException;

class BulkTest extends \PHPUnit\Framework\TestCase
{
	private static $client;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];

		static::$client = new Client($params);
		$params = [
			'table' => 'bulktest',
			'body' => [
				'columns' => [
					'title' => [
						'type' => 'text',
					],
				],
				'silent' => true,
			],
		];

		static::$client->tables()->create($params);
		static::$client->tables()->truncate(['table' => 'bulktest']);
	}

	/*
	public function testBulkInsertError() {
		static::$client->bulk(
			['body' => [
			['insert' => ['table' => 'bulktest', 'id' => 1, 'doc' => ['title' => 'test']]],
			['insert' => ['table' => 'bulktest', 'id' => 2, 'doc' => ['title' => 'test']]],
			['insert' => ['table' => 'bulktest', 'id' => 3, 'doc' => ['title' => 'test']]],
			]]
		);
		$this->expectException(ResponseException::class);
		static::$client->bulk(
			['body' => [
			['insert' => ['table' => 'bulktest', 'id' => 1, 'doc' => ['title' => 'test']]],
			['insert' => ['table' => 'bulktest', 'id' => 2, 'doc' => ['title' => 'test']]],
			['insert' => ['table' => 'bulktest', 'id' => 3, 'doc' => ['title' => 'test']]],
			]]
		);
	}*/

	public function testDelete() {
		static::$client->bulk(
			['body' => [
				['insert' => ['table' => 'bulktest', 'id' => 1, 'doc' => ['title' => 'test']]],
				['insert' => ['table' => 'bulktest', 'id' => 2, 'doc' => ['title' => 'test']]],
				['insert' => ['table' => 'bulktest', 'id' => 3, 'doc' => ['title' => 'test']]],
			]]
		);
		static::$client->search(['body' => ['table' => 'bulktest', 'query' => ['match_all' => '']]]);
		$response = static:: $client->bulk(
			['body' => [
			['insert' => ['table' => 'bulktest', 'id' => 4, 'doc' => ['title' => 'test']]],
			['delete' => ['table' => 'bulktest', 'id' => 2]],
			['delete' => ['table' => 'bulktest', 'id' => 3]],
			]]
		);

		$this->assertEquals(1, sizeof($response['items']));
		$responseKeys = array_keys($response['items'][0]);
		$this->assertEquals(1, sizeof($responseKeys));
		$this->assertEquals('bulk', array_shift($responseKeys));
		$response = static::$client->search(['body' => ['table' => 'bulktest', 'query' => ['match_all' => '']]]);
		$this->assertEquals(2, $response['hits']['total']);
	}

	public function testSetBodyAsString() {
		$bulk = new \Manticoresearch\Endpoints\Bulk();
		$bulk->setBody('some string');
		$this->assertEquals('some string', $bulk->getBody());
	}
}
