<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints\Tables;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Tables\Create;
use Manticoresearch\Exceptions\RuntimeException;

class CreateTest extends \PHPUnit\Framework\TestCase
{
	public function testCreateTableWithOptions() {
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		$client = new Client($params);
		$params = [
			'table' => 'products',
			'body' => [
				'columns' => [
					'title' => [
						'type' => 'text',
						'options' => ['indexed', 'stored', 'engine' => 'columnar'],
					],
					'price' => [
						'type' => 'float',
					],
				],
				'settings' => [
					'rt_mem_limit' => '256M',
					'min_infix_len' => '3',
				],
				'silent' => true,
			],
		];
		$response = $client->tables()->create($params);
		$this->assertSame(['total' => 0,'error' => '','warning' => ''], $response);
		$params = [
			'table' => 'products',
		];
		$response = $client->tables()->drop($params);
		$this->assertSame(['total' => 0,'error' => '','warning' => ''], $response);
	}

	public function testCreateDistributed() {
		$params = ['host' => $_SERVER['MS_HOST'], 'port' => $_SERVER['MS_PORT']];
		$client = new Client($params);
		$params = [
			'table' => 'testrt',
			'body' => [
				'columns' => [
					'title' => [
						'type' => 'text',
						'options' => ['indexed', 'stored'],
					],
				],
				'silent' => true,
			],
		];
		$response = $client->tables()->create($params);

		$params = [
			'table' => 'testrtdist',
			'body' => [
				'settings' => [
					'type' => 'distributed',
					'local' => 'testrt',
				],
			],
		];
		$response = $client->tables()->create($params);
		$this->assertSame(['total' => 0,'error' => '','warning' => ''], $response);
		$params = [
			'table' => 'testrtdist',
		];
		$response = $client->tables()->drop($params);
		$this->assertSame(['total' => 0,'error' => '','warning' => ''], $response);
	}

	public function testCreateDistributedWIthMultipleTablees() {
		$localTableName = 'testrt';
		$localTableName2 = 'testrt2';
		$distributedTableName = 'testrtdist';

		$params = ['host' => $_SERVER['MS_HOST'], 'port' => $_SERVER['MS_PORT']];
		$client = new Client($params);
		$params = [
			'table' => $localTableName,
			'body' => [
				'columns' => [
					'title' => [
						'type' => 'text',
						'options' => ['indexed', 'stored'],
					],
				],
				'silent' => true,
			],
		];
		$response = $client->tables()->create($params);
		$params = [
			'table' => $localTableName2,
			'body' => [
				'columns' => [
					'title' => [
						'type' => 'text',
						'options' => ['indexed', 'stored'],
					],
				],
				'silent' => true,
			],
		];
		$response = $client->tables()->create($params);

		$params = [
			'table' => $distributedTableName,
			'body' => [
				'settings' => [
					'type' => 'distributed',
					'local' => [
						$localTableName,
						$localTableName2,
					],
				],
			],
		];
		$response = $client->tables()->create($params);
		$this->assertSame(['total' => 0, 'error' => '', 'warning' => ''], $response);

		$response = $client->tables()->drop(
			[
			'table' => $distributedTableName,
			]
		);
		$this->assertSame(['total' => 0, 'error' => '', 'warning' => ''], $response);

		$response = $client->tables()->drop(
			[
			'table' => $localTableName,
			]
		);
		$this->assertSame(['total' => 0, 'error' => '', 'warning' => ''], $response);

		$response = $client->tables()->drop(
			[
			'table' => $localTableName2,
			]
		);
		$this->assertSame(['total' => 0, 'error' => '', 'warning' => ''], $response);
	}

	public function testNoTableDrop() {
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		$client = new Client($params);
		$params = [
			'table' => 'notablename',
			'body' => ['silent' => true],
		];
		$response = $client->tables()->drop($params);
		$this->assertSame(['total' => 0,'error' => '','warning' => ''], $response);
	}

	public function testSetGetTable() {
		$describe = new Create();
		$describe->setTable('testName');
		$this->assertEquals('testName', $describe->getTable());
	}

	public function testSetBodyNoTable() {
		$describe = new Create();
		$this->expectExceptionMessage('Table name is missing');
		$this->expectException(RuntimeException::class);
		$describe->setBody([]);
	}
}
