<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Endpoints\Nodes\Status;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class StatusTest extends \PHPUnit\Framework\TestCase
{

	public function testGetPath() {
		$status = new Status();
		$this->assertEquals('/sql', $status->getPath());
	}

	public function testGetMethod() {
		$status = new Status();
		$this->assertEquals('POST', $status->getMethod());
	}

	public function testGetStatus() {
		$helper = new PopulateHelperTest('testDummy');
		$client = $helper->getClient();
		$response = $client->nodes()->status();
		$this->assertArrayHasKey('uptime', $response);
	}
}
