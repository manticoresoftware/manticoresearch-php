<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\Utils;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
	use Utils;

	/**
	 * The trailing character is the Thai equivalent of the letter R in English, known as 'ror rua'.  The UTF8 hex
	 * value is e0b8a3, which matches in 3 pairs the escaped string below
	 * See https://www.fileformat.info/info/unicode/char/0e23/index.htm
	 */
	public function testEscapeString() {
		$this->assertEquals('thisisastringร', static::escape('thisisastringร'));
	}
}
