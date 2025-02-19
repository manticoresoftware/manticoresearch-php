<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Tables;

use Manticoresearch\Endpoints\Tables\Import;

class ImportTest extends \PHPUnit\Framework\TestCase
{

	public function testSetGetTable() {
		$describe = new Import();
		$describe->setTable('testName');
		$this->assertEquals('testName', $describe->getTable());
	}
}
