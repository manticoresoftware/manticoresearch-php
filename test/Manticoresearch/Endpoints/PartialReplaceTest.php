<?php
namespace Manticoresearch\Test\Endpoints;

class PartialReplaceTest extends \PHPUnit\Framework\TestCase
{

	public function testGetPath() {
		$replace = new \Manticoresearch\Endpoints\PartialReplace();
		$replace->setPathByIndexAndId('test', 1);
		$this->assertEquals('/test/_update/1', $replace->getPath());
	}

	public function testGetMethod() {
		$replace = new \Manticoresearch\Endpoints\Replace();
		$this->assertEquals('POST', $replace->getMethod());
	}
}
