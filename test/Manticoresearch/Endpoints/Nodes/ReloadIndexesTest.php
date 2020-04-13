<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Endpoints\Nodes\FlushLogs;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class ReloadIndexesTest  extends \PHPUnit\Framework\TestCase
{
    public function testReloadIndexes()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->reloadindexes();
        $this->assertEquals( ['total'=>0,'error'=>'','warning'=>''],$response);
    }
}
