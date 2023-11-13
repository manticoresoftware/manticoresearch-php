<?php
namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Test\Helper\PopulateHelperTest;

class DebugTest extends \PHPUnit\Framework\TestCase
{

	public function testDebug() {
		$helper = new PopulateHelperTest();
		$client = $helper->getClient();
		$response = $client->nodes()->debug(['body' => []]);

		$this->assertArrayHasKey('debug sched', $response);
	}
}
