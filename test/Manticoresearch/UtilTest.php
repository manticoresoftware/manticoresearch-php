<?php

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
    public function testEscapeString()
    {
        $this->assertEquals('thisisastring\xe0\xb8\xa3', self::escape('thisisastringร'));
    }
}
