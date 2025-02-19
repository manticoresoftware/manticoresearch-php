<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Pq;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Pq\Search;
use Manticoresearch\Exceptions\RuntimeException;

class SearchTest extends \PHPUnit\Framework\TestCase
{
	public function testMissingTableName() {
		$client = new Client();
		$params = [
			'body' => [
				'query' => [
					'percolate' => [
						'document' => [
							'subject' => 'test',
							'content' => 'some content',
							'catid' => 5,
						],
					],
				],
			],
		];
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Table name is missing.');
		$client->pq()->doc($params);
	}

	public function testSetGetTable() {
		$search = new Search();
		$search->setTable('products');
		$this->assertEquals('products', $search->getTable());
	}

	public function testMethod() {
		$search = new Search();
		$this->assertEquals('POST', $search->getMethod());
	}

	public function testGetPath() {
		$search = new Search();
		$search->setTable('products');
		$this->assertEquals('/pq/products/search', $search->getPath());
	}

	public function testGetPathTableMissing() {
		$search = new Search();
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Table name is missing');
		$search->getPath();
	}
}
