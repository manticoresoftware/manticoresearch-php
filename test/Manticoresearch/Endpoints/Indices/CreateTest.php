<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Create;
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
			'index' => 'products',
			'body' => [
				'columns' => [
					'title' => [
						'type' => 'text',
						'options' => ['indexed', 'stored'],
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
		$response = $client->indices()->create($params);
		$this->assertSame(['total' => 0,'error' => '','warning' => ''], $response);
		$params = [
			'index' => 'products',
		];
		$response = $client->indices()->drop($params);
		$this->assertSame(['total' => 0,'error' => '','warning' => ''], $response);
	}

	public function testCreateDistributed() {
		$params = ['host' => $_SERVER['MS_HOST'], 'port' => $_SERVER['MS_PORT']];
		$client = new Client($params);
		$params = [
			'index' => 'testrt',
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
		$response = $client->indices()->create($params);

		$params = [
			'index' => 'testrtdist',
			'body' => [
				'settings' => [
					'type' => 'distributed',
					'local' => 'testrt',
				],
			],
		];
		$response = $client->indices()->create($params);
		$this->assertSame(['total' => 0,'error' => '','warning' => ''], $response);
		$params = [
			'index' => 'testrtdist',
		];
		$response = $client->indices()->drop($params);
		$this->assertSame(['total' => 0,'error' => '','warning' => ''], $response);
	}

	public function testCreateDistributedWIthMultipleIndexes() {
		$localIndexName = 'testrt';
		$localIndexName2 = 'testrt2';
		$distributedIndexName = 'testrtdist';

		$params = ['host' => $_SERVER['MS_HOST'], 'port' => $_SERVER['MS_PORT']];
		$client = new Client($params);
		$params = [
			'index' => $localIndexName,
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
		$response = $client->indices()->create($params);
		$params = [
			'index' => $localIndexName2,
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
		$response = $client->indices()->create($params);

		$params = [
			'index' => $distributedIndexName,
			'body' => [
				'settings' => [
					'type' => 'distributed',
					'local' => [
						$localIndexName,
						$localIndexName2,
					],
				],
			],
		];
		$response = $client->indices()->create($params);
		$this->assertSame(['total' => 0, 'error' => '', 'warning' => ''], $response);

		$response = $client->indices()->drop(
			[
			'index' => $distributedIndexName,
			]
		);
		$this->assertSame(['total' => 0, 'error' => '', 'warning' => ''], $response);

		$response = $client->indices()->drop(
			[
			'index' => $localIndexName,
			]
		);
		$this->assertSame(['total' => 0, 'error' => '', 'warning' => ''], $response);

		$response = $client->indices()->drop(
			[
			'index' => $localIndexName2,
			]
		);
		$this->assertSame(['total' => 0, 'error' => '', 'warning' => ''], $response);
	}

	public function testNoIndexDrop() {
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		$client = new Client($params);
		$params = [
			'index' => 'noindexname',
			'body' => ['silent' => true],
		];
		$response = $client->indices()->drop($params);
		$this->assertSame(['total' => 0,'error' => '','warning' => ''], $response);
	}

	public function testSetGetIndex() {
		$describe = new Create();
		$describe->setIndex('testName');
		$this->assertEquals('testName', $describe->getIndex());
	}

	public function testSetBodyNoIndex() {
		$describe = new Create();
		$this->expectExceptionMessage('Index name is missing');
		$this->expectException(RuntimeException::class);
		$describe->setBody([]);
	}
}
