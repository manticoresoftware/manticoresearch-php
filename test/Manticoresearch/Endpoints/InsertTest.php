<?php


use Manticoresearch\Client;
use Manticoresearch\Exceptions\ConnectionException;

class InsertTest  extends \PHPUnit\Framework\TestCase
{
    public function testPath()
    {
        $insert = new \Manticoresearch\Endpoints\Insert();
        $this->assertEquals('/json/insert', $insert->getPath());
    }

    public function testGetMethod()
    {
        $insert = new \Manticoresearch\Endpoints\Insert();
        $this->assertEquals('POST', $insert->getMethod());
    }

}
