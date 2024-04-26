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

	public function testSetGetIndex() {
		$dbq = new DeleteByQuery();
		$dbq->setIndex('products');
		$this->assertEquals('products', $dbq->getIndex());
	}

	public function testMethod() {
		$dbq = new DeleteByQuery();
		$this->assertEquals('POST', $dbq->getMethod());
	}

	public function testGetPath() {
		$dbq = new DeleteByQuery();
		$dbq->setIndex('products');
		$this->assertEquals('/pq/products/_delete_by_query', $dbq->getPath());
	}

	public function testGetPathIndexMissing() {
		$dbq = new DeleteByQuery();
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Index name is missing');
		$dbq->getPath();
	}
}
