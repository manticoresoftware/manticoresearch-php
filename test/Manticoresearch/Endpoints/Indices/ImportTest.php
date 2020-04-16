<?php

namespace Manticoresearch\Test\Endpoints\Indices;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Indices\Create;
use Manticoresearch\Endpoints\Indices\Drop;
use Manticoresearch\Endpoints\Indices\Import;
use Manticoresearch\Exceptions\RuntimeException;

class ImportTest  extends \PHPUnit\Framework\TestCase
{

    public function testSetGetIndex()
    {
        $describe = new Import();
        $describe->setIndex('testName');
        $this->assertEquals('testName', $describe->getIndex());
    }

}
