<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

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
