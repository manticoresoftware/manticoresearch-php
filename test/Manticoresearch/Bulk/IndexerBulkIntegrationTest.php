<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Bulk;

use Manticoresearch\Client;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;
use PHPUnit\Framework\TestCase;

/**
 * Live tests for indexer-assisted bulk. Skipped when the server build lacks the feature.
 */
class IndexerBulkIntegrationTest extends TestCase
{
	/** @var Client */
	private static $client;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		$helper = new PopulateHelperTest('testDummy');
		static::$client = $helper->getClient();
	}

	public function testAddDocumentsFastOrSkip() {
		$tableName = 'indexer_bulk_php_' . getmypid();
		$client = static::$client;
		$client->tables()->drop(['table' => $tableName, 'body' => ['silent' => true]]);
		$client->tables()->create(
			[
				'table' => $tableName,
				'body' => [
					'columns' => [
						'title' => ['type' => 'text'],
						'gid' => ['type' => 'integer'],
					],
				],
			]
		);

		$table = $client->table($tableName);
		$docs = [];
		for ($i = 1; $i <= 50; $i++) {
			$docs[] = ['id' => $i, 'title' => 'doc ' . $i, 'gid' => $i % 5];
		}

		try {
			$result = $table->addDocumentsStreaming(
				$docs,
				[
					'workers' => 2,
				]
			);
		} catch (RuntimeException $e) {
			$client->tables()->drop(['table' => $tableName, 'body' => ['silent' => true]]);
			$this->markTestSkipped('indexer_rt_bulk unavailable: ' . $e->getMessage());
			return;
		} catch (\Manticoresearch\Exceptions\ResponseException $e) {
			$client->tables()->drop(['table' => $tableName, 'body' => ['silent' => true]]);
			$this->markTestSkipped('indexer_rt_bulk unavailable: ' . $e->getMessage());
			return;
		}

		$this->assertSame('indexer_rt_bulk', $result['mode']);
		$this->assertSame(50, $result['docs']);
		$this->assertGreaterThanOrEqual(1, $result['requests']);

		$search = $client->search(
			[
				'body' => [
					'table' => $tableName,
					'query' => ['match_all' => ''],
					'limit' => 0,
				],
			]
		);
		$this->assertSame(50, $search['hits']['total']);

		$client->tables()->drop(['table' => $tableName, 'body' => ['silent' => true]]);
	}

	public function testRejectsMissingIds() {
		$table = static::$client->table('indexer_bulk_php_invalid');
		$this->expectException(RuntimeException::class);
		$table->addDocumentsStreaming(
			[
				['title' => 'no id'],
			],
			['workers' => 1]
		);
	}
}
