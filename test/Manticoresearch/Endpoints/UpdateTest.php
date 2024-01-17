<?php
namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Update;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class UpdateTest extends \PHPUnit\Framework\TestCase
{
	/** @var Client */
	private static $client;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$helper = new PopulateHelperTest();
		$helper->populateForKeywords();
		static::$client = $helper->getClient();
	}

	public function testGetPath() {
		$update = new Update();
		$this->assertEquals('/json/update', $update->getPath());
	}

	public function testGetMethod() {
		$update = new Update();
		$this->assertEquals('POST', $update->getMethod());
	}

	/**
	 * @todo GBA: This breaks with Manticore complaining that the attribute title cannot be found.  Why?
	 */
	public function testUpdateProduct() {
		$partial = [
			'body' => [
				'index' => 'products',
				'id' => 100,
				'doc' => [
					// title cannot be updated as it is a text field, see
					// https://github.com/manticoresoftware/manticoresearch-php/issues/10#issuecomment-612685916
					'price' => 4.99, // was 2.99
				],
			],
		];
		static::$client->update($partial);

		$search = [
			'body' => [
				'index' => 'products',
				'query' => [
					'match' => ['*' => 'broken'],
				],
			],
		];
		$results = static::$client->search($search);

		$this->assertEquals(1, $results['hits']['total']);
		$this->assertEquals(4.99, $results['hits']['hits'][0]['_source']['price']);
	}
}
