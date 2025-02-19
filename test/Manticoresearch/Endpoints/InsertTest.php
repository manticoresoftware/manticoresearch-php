<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

class InsertTest extends \PHPUnit\Framework\TestCase
{
	public function testPath() {
		$insert = new \Manticoresearch\Endpoints\Insert();
		$this->assertEquals('/insert', $insert->getPath());
	}

	public function testGetMethod() {
		$insert = new \Manticoresearch\Endpoints\Insert();
		$this->assertEquals('POST', $insert->getMethod());
	}

	public function testInsert() {
		$helper = new \Manticoresearch\Test\Helper\PopulateHelperTest('testDummy');
		$helper->populateForKeywords();
		$client = $helper->getClient();

		// insert a product
		$doc = [
			'index' => 'products',
			'id' => 1001,
			'doc' => [
				'title' => 'Star Trek: Nemesis DVD',
				'price' => 6.99,
			],
		];
		$response = $client->insert(['body' => $doc]);
		unset($response['_index']);
		unset($response['table']);

		// assert inserted
		$this->assertEquals(
			[
			'_id' => 1001,
			'created' => true,
			'result' => 'created',
			'status' => 201,
			], $response
		);

		// search for inserted product
		$helper->search('products', 'Star Trek DVD', 1);

		// reinsert, this should fail due to duplicate ID
		$this->expectException(\Manticoresearch\Exceptions\ResponseException::class);
		$this->expectExceptionMessage("duplicate id '1001'");
		$client->insert(['body' => $doc]);
	}
}
