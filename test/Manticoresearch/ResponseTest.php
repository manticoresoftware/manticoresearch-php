<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
	public function testGetSetTime() {
		$response = new Response([]);
		$time = time();
		$response->setTime($time);
		$this->assertEquals($time, $response->getTime());
	}

	public function testGetSetTransportInfo() {
		$response = new Response([]);
		$transsportInfo = 'transport info';
		$response->setTransportInfo($transsportInfo);
		$this->assertEquals($transsportInfo, $response->getTransportInfo());
	}

	public function testConstructorWithArray() {
		$payload = ['test' => true];
		$response = new Response($payload);
		$this->assertEquals($payload, $response->getResponse());
	}

	public function testConstructorWithInvalidJSON() {
		$payload = '["test": this is not valid JSON';
		$response = new Response($payload);

		$this->expectException(RuntimeException::class);
		$response->getResponse();
	}
}
