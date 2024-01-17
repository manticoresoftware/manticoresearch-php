<?php
namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Test\Helper\PopulateHelperTest;

class VariablesTest extends \PHPUnit\Framework\TestCase
{
	public function testVariables() {
		$helper = new PopulateHelperTest();
		$client = $helper->getClient();
		$response = $client->nodes()->variables();

		$keys = array_keys($response);
		sort($keys);
		// Adding extra try-catch to provide compatibility with previous Manticore versions
		try {
			$this->assertEquals(
				[
				'auto_optimize',
				'autocommit',
				'character_set_client',
				'character_set_connection',
				'collation_connection',
				'grouping_in_utc',
				'last_insert_id',
				'log_level',
				'max_allowed_packet',
				'optimize_cutoff',
				'pseudo_sharding',
				'query_log_format',
				'secondary_indexes',
				'session_read_only',
				'threads_ex',
				'threads_ex_effective',
				], $keys
			);
		} catch (\PHPUnit\Framework\ExpectationFailedException $e) {
			$this->assertEquals(
				[
				'accurate_aggregation',
				'auto_optimize',
				'autocommit',
				'character_set_client',
				'character_set_connection',
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
				], $keys
			);
		}
	}

	public function testVariablesWithPattern() {
		$helper = new PopulateHelperTest();
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
		$helper = new PopulateHelperTest();
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
