<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

class PartialReplaceTest extends \PHPUnit\Framework\TestCase
{

	public function testGetPath() {
		$replace = new \Manticoresearch\Endpoints\PartialReplace();
		$replace->setPathByTableAndId('test', 1);
		$this->assertEquals('/test/_update/1', $replace->getPath());
	}

	public function testGetMethod() {
		$replace = new \Manticoresearch\Endpoints\Replace();
		$this->assertEquals('POST', $replace->getMethod());
	}
}
