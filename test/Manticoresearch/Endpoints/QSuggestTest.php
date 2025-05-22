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

class QSuggestTest extends \PHPUnit\Framework\TestCase
{
	/** @var Client */
	private static $client;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$helper = new PopulateHelperTest('testDummy');
		$helper->populateForKeywords();
		static::$client = $helper->getClient();
	}

	public function testQSuggest() {
		$params = [
			'table' => 'products',
			'body' => [
				'query' => 'brokn',
				'options' => [
					'limit' => 5,
				],
			],
		];
		$response = static::$client->qsuggest($params);
		$this->assertSame('broken', array_keys($response)[0]);
	}

	public function testQSuggestGetTable() {
		$suggest = new \Manticoresearch\Endpoints\QSuggest();
		$suggest->setTable('products');
		$this->assertEquals('products', $suggest->getTable());
	}

	public function testQSuggestNoTable() {
		$suggest = new \Manticoresearch\Endpoints\QSuggest();
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Table name is missing');
		$suggest->setBody([]);
	}
}
