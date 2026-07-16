<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Endpoints\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
	public function testTokenRequest() {
		$token = new Token(['body' => '{}']);

		$this->assertSame('/token', $token->getPath());
		$this->assertSame('POST', $token->getMethod());
		$this->assertSame('{}', $token->getBody());
		$this->assertSame('application/json', $token->getContentType());
	}
}
