<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Endpoints\Nodes\Threads;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class ThreadsTest extends \PHPUnit\Framework\TestCase
{
	public function testThreads() {
		$helper = new PopulateHelperTest('testDummy');
		$client = $helper->getClient();
		$response = $client->nodes()->threads();

		// there is only one key returned, but it is always a different number
		$table = array_keys($response);
		$response2 = $response[$table[0]];
		$this->assertArrayHasKey('Info', $response2);
	}

	public function testSetBody() {
		$threads = new Threads();

		// @todo What are better representative values here
		$threads->setBody(['ignored', 'ignored', 'red', 'yellow']);

		$this->assertEquals('mode=raw&query=SHOW+THREADS++OPTION+red%3D0%2Cyellow%3D1', $threads->getBody());
	}
}
