<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class DeleteTest extends \PHPUnit\Framework\TestCase
{
	/** @var Client */
	private static $client;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$helper = new PopulateHelperTest('testDummy');
		$helper->populateForKeywords();
		static::$client = $helper->getClient();
	}

	public function testPath() {
		$insert = new \Manticoresearch\Endpoints\Delete();
		$this->assertEquals('/delete', $insert->getPath());
	}

	public function testGetMethod() {
		$insert = new \Manticoresearch\Endpoints\Delete();
		$this->assertEquals('POST', $insert->getMethod());
	}

	public function testDelete() {
		$helper = new PopulateHelperTest('testDummy');
		$helper->search('products', 'broken', 1);
		$doc = [
			'body' => [
				'table' => 'products',
				'id' => 100,
			],
		];

		static::$client->delete($doc);
		$helper->search('products', 'broken', 0);
	}
}
