<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Endpoints\Nodes\FlushLogs;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class ReloadPluginsTest  extends \PHPUnit\Framework\TestCase
{
    /**
     * @todo How to get this to not error?
     */
    public function testReloadPlugins()
    {
        $this->markTestSkipped();
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->reloadplugins();
        $this->assertEquals( ['total'=>0,'error'=>'','warning'=>''],$response);
    }
}
