<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Response;

use Manticoresearch\Response\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
	public function testReturnsTrimmedRawToken() {
		$response = new Token(" raw-token\n", 200);

		$this->assertSame('raw-token', $response->getResponse());
		$this->assertFalse($response->hasError());
		$this->assertSame('', $response->getError());
	}

	public function testExtractsTokenFromJsonObject() {
		$response = new Token(
			'{"token":"eb83b6f0de8e9febf9cb72740533e8bf666b48f4e73800e6f0d92e75c0366e7f"}',
			200
		);

		$this->assertSame(
			'eb83b6f0de8e9febf9cb72740533e8bf666b48f4e73800e6f0d92e75c0366e7f',
			$response->getResponse()
		);
	}

	public function testExtractsTokenFromJsonString() {
		$response = new Token('"raw-token"', 200);

		$this->assertSame('raw-token', $response->getResponse());
	}

	public function testReturnsJsonErrorForFailedRequest() {
		$response = new Token('{"error":"invalid credentials"}', 401);

		$this->assertTrue($response->hasError());
		$this->assertSame('"invalid credentials"', $response->getError());
	}

	public function testReturnsPlainErrorForFailedRequest() {
		$response = new Token('Unauthorized', 401);

		$this->assertTrue($response->hasError());
		$this->assertSame('Unauthorized', $response->getError());
	}
}
