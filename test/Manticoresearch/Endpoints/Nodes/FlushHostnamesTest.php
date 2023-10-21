<?php

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Test\Helper\PopulateHelperTest;

class FlushHostnamesTest extends \PHPUnit\Framework\TestCase
{
    public function testFlushHostNames()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->flushhostnames();
        $this->assertEquals(['total' => 0,'error' => '','warning' => ''], $response);
    }
}
