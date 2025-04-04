<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Keywords;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class KeywordsTest extends \PHPUnit\Framework\TestCase
{
	/** @var Client */
	private static $client;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$helper = new PopulateHelperTest('testDummy');
		$helper->populateForKeywords();
		static::$client = $helper->getClient();
	}

	public function testKeywords() {
		$params = [
			'table' => 'products',
			'body' => [
				'query' => 'product',
				'options' => [
					'stats' => 1,
					'fold_lemmas' => 1,
				],
			],
		];
		$response = static::$client->keywords($params);
		$this->assertSame('product', $response['0']['normalized']);
	}

	public function testKeywordsBadTable() {
		$params = [
			'table' => 'productsNOT',
			'body' => [
				'query' => 'product',
				'options' => [
					'stats' => 1,
					'fold_lemmas' => 1,
				],
			],
		];

		// Adding extra try-catch to provide compatibility with previous Manticore versions
		try {
			static::$client->keywords($params);
		} catch (ResponseException $e) {
			try {
				$this->assertEquals('"no such table productsNOT"', $e->getMessage());
			} catch (\PHPUnit\Framework\ExpectationFailedException $e) {
				$this->expectException(ResponseException::class);
				$this->expectExceptionMessage('no such index productsNOT');
				static::$client->keywords($params);
			}
		}
	}

	public function testSetGetTable() {
		$kw = new Keywords();
		$kw->setTable('products');
		$this->assertEquals('products', $kw->getTable());
	}
}
