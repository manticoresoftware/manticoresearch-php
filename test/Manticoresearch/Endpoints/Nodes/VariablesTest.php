<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Test\Helper\PopulateHelperTest;

class VariablesTest extends \PHPUnit\Framework\TestCase
{
	public function testVariables() {
		$helper = new PopulateHelperTest('testDummy');
		$client = $helper->getClient();
		$response = $client->nodes()->variables();

		$keys = array_keys($response);
		sort($keys);
		$this->assertEquals(
			[
				'accurate_aggregation',
				'auto_optimize',
				'autocommit',
				'character_set_client',
				'character_set_connection',
				'cluster_user',
				'collation_connection',
				'distinct_precision_threshold',
				'grouping_in_utc',
				'last_insert_id',
				'log_level',
				'max_allowed_packet',
				'optimize_cutoff',
				'pseudo_sharding',
				'query_log_format',
				'secondary_indexes',
				'session_read_only',
				'thread_stack',
				'threads_ex',
				'threads_ex_effective',
				'timezone',
				'user',
			], $keys
		);
	}

	public function testVariablesWithPattern() {
		$helper = new PopulateHelperTest('testDummy');
		$client = $helper->getClient();
		$response = $client->nodes()->variables(['body' => ['pattern' => 'cha%']]);

		$keys = array_keys($response);
		sort($keys);
		$this->assertEquals(
			[
			'character_set_client',
			'character_set_connection',
			], $keys
		);
	}

	public function testVariablesWithWhere() {
		$this->markTestSkipped('Not sure of the functionality here');
		/*
		$helper = new PopulateHelperTest('testDummy');
		$client = $helper->getClient();
		$response = $client->nodes()->variables(['body' => ['where' => ['variable_name' => 'character_set_client' ]]]);

		$keys = array_keys($response);
		sort($keys);
		$this->assertEquals([
			'character_set_client',
			'character_set_connection',
		], $keys);
		*/
	}
}
