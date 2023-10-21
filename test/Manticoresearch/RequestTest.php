<?php

namespace Manticoresearch\Test;

use Manticoresearch\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testSetGetPath()
    {
        $request = new Request();
        $request->setPath('/some/path');
        $this->assertEquals('/some/path', $request->getPath());
    }

    public function testSetGetMethod()
    {
        $request = new Request();
        $request->setMethod('PUT');
        $this->assertEquals('PUT', $request->getMethod());
    }
}
