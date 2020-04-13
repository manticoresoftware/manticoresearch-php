<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Endpoints\Nodes\FlushLogs;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class FlushHostnamesTest  extends \PHPUnit\Framework\TestCase
{
    public function testFlushHostNames()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->flushhostnames();
        $this->assertEquals( ['total'=>0,'error'=>'','warning'=>''],$response);
    }
}
