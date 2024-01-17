<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Endpoints\Indices\Import;

class ImportTest extends \PHPUnit\Framework\TestCase
{

	public function testSetGetIndex() {
		$describe = new Import();
		$describe->setIndex('testName');
		$this->assertEquals('testName', $describe->getIndex());
	}
}
