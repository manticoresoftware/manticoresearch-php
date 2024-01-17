<?php
namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Endpoints\Nodes\Set;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class SetTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * See https://manual.manticoresearch.com/Server_settings/Setting_variables_online#SET
	 */
	public function testSet() {
		$helper = new PopulateHelperTest();
		$client = $helper->getClient();
		$payload = [
			'body' => [
				'variable' => [
					'name' => 'PROFILING',
					'value' => 0,
				],
			],
		];
		$response = $client->nodes()->set($payload);
		$this->assertEquals(['total' => 0,'error' => '','warning' => ''], $response);
	}

	public function testSetBodyNoVariable() {
		$set = new Set();
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Variable is missing for /nodes/set');
		$set->setBody([]);
	}
}
