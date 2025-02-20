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
		$helper = new PopulateHelperTest('testDummy');
		$client = $helper->getClient();

		// need to remove tables created by other tests
		$otherTables = [
		  'testrt', 'products', 'test', 'testrtdist', 'testtable', 'movies', 'bulktest',
		];
		foreach ($otherTables as $table) {
			$client->tables()->drop(
				[
					'table' => $table,
					'body' => ['silent' => true],
				]
			);
		}


		$helper->populateForKeywords();

		$helper = new PopulateHelperTest('testDummy');
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
