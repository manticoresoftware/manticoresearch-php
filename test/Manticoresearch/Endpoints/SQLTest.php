<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

class SQLTest extends \PHPUnit\Framework\TestCase
{
	public function testPath() {
		$sql = new \Manticoresearch\Endpoints\Sql();
		$this->assertEquals('/sql', $sql->getPath());
	}

	public function testSetGetMode() {
		$sql = new \Manticoresearch\Endpoints\Sql();
		$sql->setMode('COOLMODE');
		$this->assertEquals('COOLMODE', $sql->getMode());
	}
}
