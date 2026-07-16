<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\TokenResponse;
use PHPUnit\Framework\TestCase;

class TokenResponseTest extends TestCase
{
	public function testReturnsTrimmedRawToken() {
		$response = new TokenResponse(" raw-token\n", 200);

		$this->assertSame('raw-token', $response->getResponse());
		$this->assertFalse($response->hasError());
		$this->assertSame('', $response->getError());
	}

	public function testReturnsJsonErrorForFailedRequest() {
		$response = new TokenResponse('{"error":"invalid credentials"}', 401);

		$this->assertTrue($response->hasError());
		$this->assertSame('"invalid credentials"', $response->getError());
	}

	public function testReturnsPlainErrorForFailedRequest() {
		$response = new TokenResponse('Unauthorized', 401);

		$this->assertTrue($response->hasError());
		$this->assertSame('Unauthorized', $response->getError());
	}
}
