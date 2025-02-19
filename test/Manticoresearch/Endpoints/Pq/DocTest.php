<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Pq;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Pq\Doc;
use Manticoresearch\Exceptions\RuntimeException;

class DocTest extends \PHPUnit\Framework\TestCase
{
	public function testMissingTableName() {
		$client = new Client();
		$params = [

			'body' => [
				'query' => ['match' => ['subject' => 'test']],
				'tags' => ['test1','test2'],
			],
		];
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Table name is missing.');
		$client->pq()->doc($params);
	}

	public function testSetGetTable() {
		$doc = new Doc();
		$doc->setTable('products');
		$this->assertEquals('products', $doc->getTable());
	}

	public function testSetGetID() {
		$doc = new Doc();
		$doc->setId(4);
		$this->assertEquals(4, $doc->getId());
	}

	public function testGetPathNoID() {
		$doc = new Doc();
		$doc->setTable('products');
		$this->assertEquals('/pq/products/doc', $doc->getPath());
	}

	public function testGetPathWithID() {
		$doc = new Doc();
		$doc->setTable('products');
		$doc->setId(4);
		$this->assertEquals('/pq/products/doc/4', $doc->getPath());
	}

	public function testMethod() {
		$dbq = new Doc();
		$this->assertEquals('POST', $dbq->getMethod());
	}
}
