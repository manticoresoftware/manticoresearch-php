<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Test\Helper\PopulateHelperTest;

class TablesTest extends \PHPUnit\Framework\TestCase
{
	public function testTables() {
		$helper = new PopulateHelperTest();
		$client = $helper->getClient();

		// need to remove indexes created by other tests
		$otherIndexes = [
		  'testrt', 'products', 'test', 'testrtdist', 'testindex', 'movies', 'bulktest',
		];
		foreach ($otherIndexes as $index) {
			$client->indices()->drop(
				[
					'index' => $index,
					'body' => ['silent' => true],
				]
			);
		}


		$helper->populateForKeywords();

		$helper = new PopulateHelperTest();
		$client = $helper->getClient();
		$response = $client->nodes()->tables();
		$result = [
			[
				'Table' => 'products',
				'Type' => 'rt',
			],
		];
		$this->assertEquals($result, $response);
	}
}
