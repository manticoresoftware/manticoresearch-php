<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Test\Helper\PopulateHelperTest;

class DebugTest extends \PHPUnit\Framework\TestCase
{

	public function testDebug() {
		$helper = new PopulateHelperTest('testDummy');
		$client = $helper->getClient();
		$response = $client->nodes()->debug(['body' => []]);

		$this->assertArrayHasKey('debug sched', $response);
	}
}
