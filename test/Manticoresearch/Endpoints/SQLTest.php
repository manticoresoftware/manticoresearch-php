<?php


use Manticoresearch\Client;
use Manticoresearch\Exceptions\ConnectionException;

class SQLTest  extends \PHPUnit\Framework\TestCase
{
    public function testPath()
    {
        $sql = new \Manticoresearch\Endpoints\Sql();
        $this->assertEquals('/sql', $sql->getPath());
    }

    public function testSetGetMode()
    {
        $sql = new \Manticoresearch\Endpoints\Sql();
        $sql->setMode('COOLMODE');
        $this->assertEquals('COOLMODE', $sql->getMode());
    }

}
