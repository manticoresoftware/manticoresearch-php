<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\Client;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\MatchQuery;
use Manticoresearch\Query\Range;
use Manticoresearch\ResultHit;
use Manticoresearch\Table;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
	protected $table;

	protected function getTable($keywords = false): Table {
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		$this->table = new Table(new Client($params));
		$this->table->setName('testtable');
		$this->table->drop(true);

		$options = [];
		if ($keywords === true) {
			$options = [
				'dict' => 'keywords',
				'min_infix_len' => 2,
			];
		}

		// for coverage purposes, does not affect functionality as table is already dropped silently
		$options['silent'] = true;

		$this->table->create(
			[
			'title' => ['type' => 'text'],
			'gid' => ['type' => 'int'],
			'label' => ['type' => 'string'],
			'tags' => ['type' => 'multi'],
			'props' => ['type' => 'json'],
			], $options
		);
		return $this->table;
	}

	protected function addDocument($table) {
		$table->addDocument(
			[
			'title' => 'This is an example document for testing',
			'gid' => 1,
			'label' => 'not used',
			'tags' => [1, 2, 3],
			'props' => [
				'color' => 'blue',
				'rule' => ['one', 'two'],
			],
			], 1
		);
	}


	public function testReplaceDocument() {
		$table = $this->getTable();
		$this->addDocument($table);
		$response = $table->replaceDocument(
			[
			'title' => 'This is an example document for cooking',
			'gid' => 1,
			'label' => 'not used',
			'tags' => [1, 2, 3],
			'props' => [
				'color' => 'blue',
				'rule' => ['one', 'two'],
			],
			], 1
		);

		$this->assertEquals(
			[
			'_id' => 1,
			'created' => false,
			'result' => 'updated',
			'status' => 200,
			'table' => 'testtable',
			], $response
		);
	}

	public function testPartialReplaceDocument() {
		$table = $this->getTable();
		$this->addDocument($table);
		$response = $table->replaceDocument(
			[
				'title' => 'This is an example document for cooking',
				'label' => 'not used',
			], 1, true
		);

		$this->assertEquals(
			[
				'_index' => 'testtable',
				'updated' => 1,
			], $response
		);
	}

	public function testReplaceDocuments() {
		$table = $this->getTable();
		$this->addDocument($table);
		$response = $table->replaceDocuments(
			[[
			'id' => 1,
			'title' => 'This is an example document for cooking',
			'gid' => 1,
			'label' => 'not used',
			'tags' => [1, 2, 3],
			'props' => [
				'color' => 'blue',
				'rule' => ['one', 'two'],
			],
			]]
		);

		$this->assertEquals(
			[
			'items' => [
				['bulk' => [
					'_id' => 1,
					'created' => 1,
					'deleted' => 1,
					'updated' => 0,
					'result' => 'updated',
					'status' => 200,
					'table' => 'testtable',
				]],
			],
			'errors' => false,
			'current_line' => 2,
			'skipped_lines' => 0,
			'error' => '',
			], $response
		);
	}

	public function testDeleteDocumentsByIds() {
		$table = $this->getTable();

		$table->addDocuments(
			[
			['id' => 1, 'title' => 'First document'],
			['id' => 2, 'title' => 'Second document'],
			['id' => 3, 'title' => 'Third document'],
			['id' => 4, 'title' => 'Fourth document'],
			['id' => 5, 'title' => 'Fifth document'],
			]
		);

		$table->deleteDocumentsByIds([2, 4]);
		$documents = $table->getDocumentByIds([1, 2, 3, 4, 5]);
		$remainingIds = [];
		foreach ($documents as $document) {
			$remainingIds[] = $document->getId();
		}
		$this->assertEquals([1, 3, 5], $remainingIds);
	}

	public function testClassOfHit() {
		$table = $this->getTable();
		$this->addDocument($table);
		$hit = $table->getDocumentById(1);
		$this->assertInstanceOf(ResultHit::class, $hit);
	}

	public function testClassOfNonExistentHit() {
		$table = $this->getTable();
		$this->addDocument($table);
		$hit = $table->getDocumentById(2);
		$this->assertNull($hit);
	}

	public function testUpdateTagsThenDeleteDocument() {
		$table = $this->getTable();
		$this->addDocument($table);
		$update = $table->updateDocument(['tags' => [10, 12, 14]], 1);
		$this->assertEquals($update['_id'], 1);

		$table->deleteDocument(1);
		$this->assertEquals($update['_id'], 1);

		$result = $table->getDocumentById(1);
		$this->assertNull($result);
	}

	public function testStatus() {
		$table = $this->getTable();
		$this->addDocument($table);
		$status = $table->status();
		$this->assertEquals(1, $status['indexed_documents']);

		$this->assertArrayHasKey('disk_bytes', $status);
	}


	public function testDescribe() {
		$table = $this->getTable();
		$keys = array_keys($table->describe());
		sort($keys);
		$this->assertEquals(
			[
			'gid',
			'id',
			'label',
			'props',
			'tags',
			'title',
			], $keys
		);
	}

	public function testAlterDrop() {
		$table = $this->getTable();
		$response = $table->alter('drop', 'props');
		$this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);

		// use describe to demonstrate the field has been removed
		$keys = array_keys($table->describe());
		sort($keys);
		$this->assertEquals(
			[
			'gid',
			'id',
			'label',
			'tags',
			'title',
			], $keys
		);
	}

	public function testAlterAdd() {
		$table = $this->getTable();
		$response = $table->alter('add', 'example', 'string');
		$this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);

		// use describe to demonstrate the field has been removed
		$description = $table->describe();
		$keys = array_keys($description);
		sort($keys);
		$this->assertEquals(
			[
			'example',
			'gid',
			'id',
			'label',
			'props',
			'tags',
			'title',
			], $keys
		);

		$this->assertEquals(['Type' => 'string', 'Properties' => ''], $description['example']);
	}

	public function testAlterInvalidOperation() {
		$table = $this->getTable();
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Alter operation not recognized');
		$table->alter('invalidOperation', 'example', 'string');
	}

	public function testTruncate() {
		$table = $this->getTable();
		$response = $table->truncate();
		$this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);
	}

	public function testOptimze() {
		$table = $this->getTable();
		$response = $table->optimize(true);
		$this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);
	}

	public function testFlush() {
		$table = $this->getTable();
		$response = $table->flush();

		// @todo Is this correct?
		$this->assertEquals(null, $response);
	}

	public function testFlushRamChunk() {
		$table = $this->getTable();
		$response = $table->flushramchunk();

		// @todo Is this correct?
		$this->assertEquals(null, $response);
	}


	public function testSearch() {
		$table = $this->getTable();
		$this->addDocument($table);
		$result = $table->search('testing')->get();
		$this->assertCount(1, $result);
		$table->drop();
	}

	public function testTableSuggest() {
		$table = $this->getTable(true);
		$this->addDocument($table);
		$result = $table->suggest('tasting', []);
		$this->assertEquals(['distance' => 1, 'docs' => 1], $result['testing']);
	}

	public function testTableExplainQuery() {
		$table = $this->getTable(true);
		$result = $table->explainQuery('test');
		$this->assertEquals('AND(KEYWORD(test, querypos=1))', $result['transformed_tree']);
	}

	public function testTableKeywords() {
		$table = $this->getTable(true);
		$this->addDocument($table);
		$result = $table->keywords('tasting', []);

		// @todo Is this correct functionality
		$this->assertEquals(['tokenized' => 'tasting', 'normalized' => 'tasting', 'qpos' => '1'], $result[0]);
	}

	public function testStart() {
		$table = $this->getTable();

		$table->setName('test');
		$table->drop(true);
		$table->create(
			['title' => [
				'type' => 'text'],
				'plot' => ['type' => 'text'],
				'_year' => ['type' => 'integer'],
				'rating' => ['type' => 'float'],
			],
			[],
			true
		);
		$table->addDocument(
			[
				'title' => 'Star Trek: Nemesis',
				'plot' => 'The Enterprise is diverted to the Romulan homeworld Romulus, supposedly because they want' .
					' to negotiate a peace treaty. Captain Picard and his crew discover a serious threat to the ' .
					'Federation once Praetor Shinzon plans to attack Earth.',
				'_year' => 2002,
				'rating' => 6.4,
			],
			1
		);

		$table->addDocuments(
			[
			['id' => 2, 'title' => 'Interstellar', 'plot' => 'A team of explorers travel through a wormhole in space' .
				' in an attempt to ensure humanity\'s survival.', '_year' => 2014, 'rating' => 8.5],
			['id' => 3, 'title' => 'Inception', 'plot' => 'A thief who steals corporate secrets through the use of' .
				' dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.',
				'_year' => 2010, 'rating' => 8.8],
			['id' => 4, 'title' => '1917 ', 'plot' => ' As a regiment assembles to wage war deep in enemy territory,' .
				' two soldiers are assigned to race against time and deliver a message that will stop 1,600 men from' .
				' walking straight into a deadly trap.', '_year' => 2018, 'rating' => 8.4],
			['id' => 5, 'title' => 'Alien', 'plot' => ' After a space merchant vessel receives an unknown transmission'.
				' as a distress call, one of the team\'s member is attacked by a mysterious life form and they soon' .
				' realize that its life cycle has merely begun.', '_year' => 1979, 'rating' => 8.4],
			]
		);

		for ($i = 6; $i <= 30; $i++) {
			$table->addDocument(
				[
					'title' => 'Star Trek: Nemesis',
					'plot' => 'The Enterprise is diverted to the Romulan homeworld Romulus, supposedly because they' .
					' want to negotiate a peace treaty. Captain Picard and his crew discover a serious threat to' .
					' the Federation once Praetor Shinzon plans to attack Earth.',
					'_year' => 2002,
					'rating' => 6.4,
				],
				$i
			);
		}

		$results = $table->search('space team')->get();

		foreach ($results as $hit) {
			$this->assertInstanceOf(ResultHit::class, $hit);
		}

		$results = $table->search('alien')
			->filter('_year', 'gte', 2000)
			->filter('rating', 'gte', 8.0)
			->sort('_year', 'desc')
			->highlight()
			->get();

		foreach ($results as $hit) {
			$this->assertInstanceOf(ResultHit::class, $hit);
		}

		$response = $table->updateDocument(['_year' => 2019], 4);
		$this->assertEquals(4, $response['_id']);

		$schema = $table->describe();
		$this->assertCount(5, $schema);

		$response = $table->updateDocuments(['_year' => 2000], ['match' => ['*' => 'team']]);
		$this->assertEquals(2, $response['updated']);

		$response = $table->updateDocuments(['_year' => 2000], new MatchQuery('team', '*'));
		$this->assertEquals(2, $response['updated']);

		$bool = new BoolQuery();
		$bool->must(new MatchQuery('team', '*'));
		$bool->must(new Range('rating', ['gte' => 8.5]));
		$response = $table->updateDocuments(['_year' => 2000], $bool);
		$this->assertEquals(1, $response['updated']);

		$response = $table->deleteDocument(4);
		$this->assertEquals(4, $response['_id']);

		$response = $table->deleteDocumentsByIds([100]);
		$this->assertEquals('not found', $response['result']);

		$response = $table->deleteDocumentsByIds([5,6]);
		$this->assertEquals(5, $response['_id']);

		$response = $table->deleteDocumentsByIds(range(7, 30));
		$this->assertEquals(7, $response['_id']);
		$docTotal = $table->search('')
			->get()
			->getTotal();
		$this->assertEquals(3, $docTotal);

		$response = $table->deleteDocuments(new Range('id', ['gte' => 100]));
		$this->assertEquals(0, $response['deleted']);


		$table->truncate();
		$results = $table->search('')
			->get();
		$this->assertCount(0, $results);

		$newdoc = '{"title":"Tenet","plot":"Armed with only one word, Tenet, and fighting for the survival of the '.
			'entire world, a Protagonist journeys through a twilight world of international espionage on a mission '.
			'that will unfold in something beyond real time","_year":2020,"rating":8.8}';
		$table->addDocument($newdoc);
		$table->addDocument(json_decode($newdoc));
		$results = $table->search('tenet')->get();
		$this->assertCount(2, $results);


		$response = $table->drop();
		$this->assertEquals('', $response['error']);
	}

	public function testPercolates() {
		$this->getTable();
		$this->table->setName('pqtest');
		$this->table->drop(true);
		$this->table->create(
			['title' => ['type' => 'text'], 'gid' => ['type' => 'integer']],
			['type' => 'percolate'],
			true
		);
		$this->table->addDocument(['query' => 'find me'], 6);
		$this->table->addDocument(['query' => 'fast'], 8);
		$this->table->addDocument(['query' => 'something'], 7);
		$docs = [
			['title' => 'find me fast'],
			['title' => 'pick me'],
			['title' => 'something else'],
			['title' => 'this is false'],
		];
		$result = $this->table->percolate($docs);
		$this->assertEquals(3, $result->count());
		$result = $this->table->percolateToDocs($docs);
		$this->assertEquals(4, $result->count());
		$result->rewind();
		$doc = $result->current();
		$this->assertTrue($doc->hasQueries());
		$this->table->drop();
	}

	public function testGetClient() {
		$table = $this->getTable();
		$this->assertInstanceOf(Client::class, $table->getClient());
	}


	public function testSetGetName() {
		$table = $this->getTable();
		$this->assertEquals('testtable', $table->getName());
	}

	public function testRawSelectQuery() {
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		$client = new Client($params);

		$table = $this->getTable();

		$table->setName('test');
		$table->drop(true);
		$table->create(
			[
				'title' => ['type' => 'text'],
				'plot' => ['type' => 'text'],
				'_year' => ['type' => 'integer'],
				'rating' => ['type' => 'float'],
			],
			[],
			true
		);
		$table->addDocument(
			[
				'title' => 'Star Trek: Nemesis',
				'plot' => 'The Enterprise is diverted to the Romulan homeworld Romulus, supposedly because they want' .
					' to negotiate a peace treaty. Captain Picard and his crew discover a serious threat to the ' .
					'Federation once Praetor Shinzon plans to attack Earth.',
				'_year' => 2002,
				'rating' => 6.4,
			],
			1
		);
		$result = $client->sql(
			[
			'mode' => 'raw',
			'body' => [
				'query' => 'select id, _year from test where _year = 2000',
			],
			]
		);
		$this->assertEquals([], $result);

		$result = $client->sql('select id, _year from test where _year = 2000', true);
		$this->assertEquals([], $result);
	}
}
