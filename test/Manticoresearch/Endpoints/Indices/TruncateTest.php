<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Truncate;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class TruncateTest extends \PHPUnit\Framework\TestCase
{
	/** @var Client */
	private static $client;

	/** @var PopulateHelperTest */
	private static $helper;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$helper = new PopulateHelperTest('testDummy');
		$helper->populateForKeywords();
		static::$client = $helper->getClient();
		static::$helper = $helper;
	}

	public function testIndexTruncate() {
		$response = static::$client->indices()->truncate(['index' => 'products']);

		$this->assertEquals(['total' => 0,'error' => '','warning' => ''], $response);
	}

	public function testSetGetIndex() {
		$describe = new Truncate();
		$describe->setIndex('testName');
		$this->assertEquals('testName', $describe->getIndex());
	}

	public function testSetBodyNoIndex() {
		$describe = new Truncate();
		$this->expectExceptionMessage('Index name is missing.');
		$this->expectException(RuntimeException::class);
		$describe->setBody([]);
	}
}
