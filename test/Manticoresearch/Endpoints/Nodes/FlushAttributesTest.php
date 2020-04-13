<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Endpoints\Nodes\FlushLogs;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class FlushAttributesTest  extends \PHPUnit\Framework\TestCase
{
    public function testFlushAttributes()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();

        // @todo This fails
        $response = $client->nodes()->flushattributes();

        $this->assertEquals( ['total'=>0,'error'=>'','warning'=>''],$response);
    }
}
