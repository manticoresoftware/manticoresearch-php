<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
	public function testSetGetPath() {
		$request = new Request();
		$request->setPath('/some/path');
		$this->assertEquals('/some/path', $request->getPath());
	}

	public function testSetGetMethod() {
		$request = new Request();
		$request->setMethod('PUT');
		$this->assertEquals('PUT', $request->getMethod());
	}
}
