<?php
namespace Manticoresearch\Test\Endpoints;

class ReplaceTest extends \PHPUnit\Framework\TestCase
{

	public function testGetPath() {
		$replace = new \Manticoresearch\Endpoints\Replace();
		$this->assertEquals('/json/replace', $replace->getPath());
	}

	public function testGetMethod() {
		$replace = new \Manticoresearch\Endpoints\Replace();
		$this->assertEquals('POST', $replace->getMethod());
	}
}
