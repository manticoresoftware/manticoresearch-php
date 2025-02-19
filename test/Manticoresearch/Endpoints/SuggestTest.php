<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class SuggestTest extends \PHPUnit\Framework\TestCase
{
	/** @var Client */
	private static $client;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$helper = new PopulateHelperTest('testDummy');
		$helper->populateForKeywords();
		static::$client = $helper->getClient();
	}
	public function testSuggest() {
		$params = [
			'table' => 'products',
			'body' => [
				'query' => 'brokn',
				'options' => [
					'limit' => 5,
				],
			],
		];
		$response = static::$client->suggest($params);
		$this->assertSame('broken', array_keys($response)[0]);
	}
	public function testSuggestBadTable() {
		$params = [
			'table' => 'productsNOT',
			'body' => [
				'query' => 'brokn',
				'options' => [
					'limit' => 5,
				],
			],
		];
		// Adding extra try-catch to provide compatibility with previous Manticore versions
		try {
			static::$client->suggest($params);
		} catch (\Manticoresearch\Exceptions\ResponseException $e) {
			try {
				$this->assertEquals('"no such index productsNOT"', $e->getMessage());
			} catch (\PHPUnit\Framework\ExpectationFailedException $e) {
				$this->expectException(\Manticoresearch\Exceptions\ResponseException::class);
				$this->expectExceptionMessage('no such table productsNOT');
				static::$client->suggest($params);
			}
		}
	}
	public function testResponseExceptionViaSuggest() {
		$params = [
			'table' => 'productsNOT',
			'body' => [
				'query' => 'brokn',
				'options' => [
					'limit' => 5,
				],
			],
		];

		try {
			$response = static::$client->suggest($params);
		} catch (ResponseException $ex) {
			$request = $ex->getRequest();
			$this->assertEquals(
				'mode=raw&query=CALL+SUGGEST%28%27brokn%27%2C%27productsNOT%27%2C5+AS+limit%29',
				$request->getBody()
			);

			$response = $ex->getResponse();
			// Adding extra try-catch to provide compatibility with previous Manticore versions
			try {
				$this->assertEquals('"no such index productsNOT"', $response->getError());
			} catch (\PHPUnit\Framework\ExpectationFailedException $e) {
				$this->assertEquals('"no such table productsNOT"', $response->getError());
			}
		}
	}
	public function testSuggestGetTable() {
		$suggest = new \Manticoresearch\Endpoints\Suggest();
		$suggest->setTable('products');
		$this->assertEquals('products', $suggest->getTable());
	}
	public function testSuggestNoTable() {
		$suggest = new \Manticoresearch\Endpoints\Suggest();
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Table name is missing');
		$suggest->setBody([]);
	}
}
