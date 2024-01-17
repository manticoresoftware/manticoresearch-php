<?php
namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Endpoints\Nodes\Status;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class StatusTest extends \PHPUnit\Framework\TestCase
{

	public function testGetPath() {
		$status = new Status();
		$this->assertEquals('/sql', $status->getPath());
	}

	public function testGetMethod() {
		$status = new Status();
		$this->assertEquals('POST', $status->getMethod());
	}

	public function testGetStatus() {
		$helper = new PopulateHelperTest();
		$client = $helper->getClient();
		$response = $client->nodes()->status();
		$this->assertArrayHasKey('uptime', $response);
	}
}
