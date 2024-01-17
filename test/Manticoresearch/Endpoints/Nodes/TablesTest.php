<?php
namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Test\Helper\PopulateHelperTest;

class TablesTest extends \PHPUnit\Framework\TestCase
{
	public function testTables() {
		$helper = new PopulateHelperTest();
		$client = $helper->getClient();

		// need to remove indexes created by other tests
		$otherIndexes = [
		  'testrt', 'products', 'test', 'testrtdist', 'testindex', 'movies', 'bulktest',
		];
		foreach ($otherIndexes as $index) {
			$client->indices()->drop(
				[
					'index' => $index,
					'body' => ['silent' => true],
				]
			);
		}


		$helper->populateForKeywords();

		$helper = new PopulateHelperTest();
		$client = $helper->getClient();
		$response = $client->nodes()->tables();
		// Adding extra try-catch to provide compatibility with previous Manticore versions
		try {
			$this->assertEquals(['products' => 'rt'], $response);
		} catch (\Manticoresearch\Exceptions\ResponseException $e) {
			$this->assertEquals(['test' => 'rt'], $response);
		}
	}
}
