<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Endpoints\Nodes\FlushLogs;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class ReloadPluginsTest  extends \PHPUnit\Framework\TestCase
{
    public function testReloadPlugins()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->reloadplugins();
        $this->assertEquals( ['total'=>0,'error'=>'','warning'=>''],$response);
    }
}
