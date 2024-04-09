<?php

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
