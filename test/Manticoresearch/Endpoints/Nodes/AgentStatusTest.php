<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class AgentStatusTest extends \PHPUnit\Framework\TestCase
{

	public function testGetPath() {
		$agentStatus = new AgentStatus();
		$this->assertEquals('/sql', $agentStatus->getPath());
	}

	public function testGetMethod() {
		$agentStatus = new AgentStatus();
		$this->assertEquals('POST', $agentStatus->getMethod());
	}

	public function testGetStatus() {
		$helper = new PopulateHelperTest('testDummy');
		$client = $helper->getClient();
		$response = $client->nodes()->agentstatus();

		// cannot test values, uptime will never be consistent.  As such use keys instead
		$keys = array_keys($response);
		sort($keys);

		$this->assertEquals(
			[
			'status_period_seconds',
			'status_stored_periods',
			], $keys
		);
	}
}
