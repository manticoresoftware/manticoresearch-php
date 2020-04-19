<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Test\Helper\PopulateHelperTest;

class FlushAttributesTest  extends \PHPUnit\Framework\TestCase
{
    public function testFlushAttributes()
    {
        $this->markTestSkipped(); // @todo Fix this test

        $helper = new PopulateHelperTest();
        $client = $helper->getClient();

        // @todo This fails
        $response = $client->nodes()->flushattributes();

        $this->assertEquals( ['total'=>0,'error'=>'','warning'=>''],$response);
    }
}
