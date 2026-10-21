<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\Exceptions\ResponseException;
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

	public function test5xxInvalidJsonResponseException() {
		$payload = '{invalid: json]';
		$response = new Response($payload, 503);

		$this->expectException(ResponseException::class);
		$this->expectExceptionMessage('HTTP 503: Syntax error');
		$this->expectExceptionCode(503);
		$response->getResponse();
	}

	public function testBigintConversion() {
		$payload = '{"id":18446744073709551615}';
		$response = new Response($payload);
		$response->enableBigintConversion();

		$this->assertSame(['id' => '18446744073709551615'], $response->getResponse());
		$this->assertSame(['id' => '18446744073709551615'], $response->getResponse());
		$this->assertIsFloat((new Response($payload))->getResponse()['id']);
	}

	public function test504InvalidJsonWithBigintConversion() {
		$response = new Response('{invalid: json]', 504);
		$response->enableBigintConversion();

		$this->expectException(ResponseException::class);
		$this->expectExceptionMessage('HTTP 504: Syntax error');
		$this->expectExceptionCode(504);
		$response->getResponse();
	}

	public function testRetryableStatusWithValidJson() {
		foreach ([503, 504] as $status) {
			foreach (['{}', '{"message":"upstream timeout"}', 'null', '"timeout"'] as $body) {
				$response = new Response($body, $status);
				$this->assertTrue($response->hasError());
				$this->assertSame($status, $response->getStatusCode());
			}
		}
		$this->assertFalse((new Response('{"message":"ok"}', 200))->hasError());
	}
}
