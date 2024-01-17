<?php


namespace Manticoresearch\Test;

use Manticoresearch\Client;
use Manticoresearch\Index;
use PHPUnit\Framework\TestCase;

class ClusterTest extends TestCase
{

	public function testCluster() {
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		//client for node 1
		$client = new Client($params);

		$params = [
			'host' => $_SERVER['MS_HOST2'],
			'port' => $_SERVER['MS_PORT2'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		//client for node 2
		$client2 = new Client($params);

		//create cluster on node 1
		$params = [
			'cluster' => 'testcluster',
			'body' => [
			],
		];
		$result = $client->cluster()->create($params);
		$this->assertEquals('', $result['error']);

		//join cluster from node 2
		$params = [
			'cluster' => 'testcluster',
			'body' => [
				'node' => 'manticoresearch-manticore:9312',
			],
		];
		$result = $client2->cluster()->join($params);
		$this->assertEquals('', $result['error']);

		//create index on node 1
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
		$client->indices()->create($params);

		//add index to cluster
		$params = [
			'cluster' => 'testcluster',
			'body' => [
				'operation' => 'add',
				'index' => 'products',
			],
		];
		$result = $client->cluster()->alter($params);
		$this->assertEquals('', $result['error']);

		//add document to index
		$doc = [
			'index' => 'products',
			'cluster' => 'testcluster',
			'id' => 1000,
			'doc' => [
				'title' => 'Star Trek: Nemesis DVD',
				'price' => 6.99,
			],
		];
		$client->insert(['body' => $doc]);

		//add document via Index class
		$index = new Index($client);
		$index->setName('products');
		$index->setCluster('testcluster');
		$result = $index->addDocument(['title' => 'The Dark Knight','price' => 7.5], 2000);

		// workaround against unstable tests. For some reason the replication which
		// has to be synchronous acts like if it was asynchronous
		sleep(3);

		//check if documents replicated on node 2
		$params = [
			'body' => [
				'index' => 'products',
				'query' => [
					'range' => ['id' => ['gte' => 500]],
				],
			],
		];
		$result = $client2->search($params);
		$this->assertEquals(2, $result['hits']['total']);

		//drop index from cluster
		$params = [
			'cluster' => 'testcluster',
			'body' => [
				'operation' => 'drop',
				'index' => 'products',
			],
		];
		$result = $client->cluster()->alter($params);
		$this->assertEquals('', $result['error']);

		sleep(5);

		// drop cluster
		$params = [
			'cluster' => 'testcluster',
			'body' => [
			],
		];
		$result = $client->cluster()->delete($params);
		$this->assertEquals('', $result['error']);

		// drop index on
		$result = $client->indices()->drop(['index' => 'products']);
		$this->assertEquals('', $result['error']);
	}
}
