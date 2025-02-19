<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Pq;

use Manticoresearch\Endpoints\Pq\DeleteByQuery;
use Manticoresearch\Exceptions\RuntimeException;

class DeleteByQueryTest extends \PHPUnit\Framework\TestCase
{

	public function testSetGetTable() {
		$dbq = new DeleteByQuery();
		$dbq->setTable('products');
		$this->assertEquals('products', $dbq->getTable());
	}

	public function testMethod() {
		$dbq = new DeleteByQuery();
		$this->assertEquals('POST', $dbq->getMethod());
	}

	public function testGetPath() {
		$dbq = new DeleteByQuery();
		$dbq->setTable('products');
		$this->assertEquals('/pq/products/_delete_by_query', $dbq->getPath());
	}

	public function testGetPathTableMissing() {
		$dbq = new DeleteByQuery();
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Table name is missing');
		$dbq->getPath();
	}
}
