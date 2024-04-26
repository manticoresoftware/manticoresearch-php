<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Settings;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class SettingsTest extends \PHPUnit\Framework\TestCase
{
	/** @var Client */
	private static $client;

	/** @var PopulateHelperTest */
	private static $helper;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$helper = new PopulateHelperTest();
		$helper->populateForKeywords();
		static::$client = $helper->getClient();
		static::$helper = $helper;
	}

	public function testSettings() {
		$response = static::$client->indices()->settings(['index' => 'products']);

		$expectedSettings = "min_infix_len = 3\nrt_mem_limit = 268435456" ;

		$this->assertEquals(['settings' => $expectedSettings], $response);
	}

	public function testSetGetIndex() {
		$describe = new Settings();
		$describe->setIndex('testName');
		$this->assertEquals('testName', $describe->getIndex());
	}

	public function testSetBodyNoIndex() {
		$describe = new Settings();
		$this->expectExceptionMessage('Index name is missing.');
		$this->expectException(RuntimeException::class);
		$describe->setBody([]);
	}
}
