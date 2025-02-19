<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Helper;

use Manticoresearch\Client;

class PopulateHelperTest extends \PHPUnit\Framework\TestCase
{
	/** @var Client */
	private $client;

	public function getClient() {
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		$this->client = new Client($params);
		return $this->client;
	}

	public function populateForKeywords() {
		$this->getClient();

		$this->client->tables()->drop(
			[
			'table' => 'products',
				'body' => ['silent' => true],
			]
		);

		$params = [
			'table' => 'products',
			'body' => [
				'columns' => [
					'title' => [
						'type' => 'text',
						'options' => ['indexed', 'stored'],
					],
					'price' => [
						'type' => 'float',
					],
				],
				'settings' => [
					'rt_mem_limit' => '256M',
					'min_infix_len' => '3',
				],
				'silent' => true,
			],
		];
		$this->client->tables()->create($params);
		$this->client->replace(
			[
			'body' => [
				'table' => 'products',
				'id' => 100,
				'doc' => [
					'title' => 'this product is not broken',
					'price' => 2.99,
				],
			],
			]
		);
	}

	public function search($tableName, $query, $numberOfResultsExpected) {
		$this->getClient();

		$search = [
			'body' => [
				'table' => $tableName,
				'query' => [
					'match' => ['*' => $query],
				],
			],
		];
		$results = $this->client->search($search);
		$actualTotal = $results['hits']['total'];
		$this->assertEquals($numberOfResultsExpected, $actualTotal);
		return $results;
	}

	public function describe($tableName) {
		return $this->client->tables()->describe(['table' => $tableName]);
	}

	public function testDummy() {
		$a = 1;
		$this->assertEquals(1, $a);
	}
}
