<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class AgentStatusTest  extends \PHPUnit\Framework\TestCase
{

    public function testGetPath()
    {
        $agentStatus = new AgentStatus();
        $this->assertEquals('/sql', $agentStatus->getPath());
    }

    public function testGetMethod()
    {
        $agentStatus = new AgentStatus();
        $this->assertEquals('POST', $agentStatus->getMethod());
    }

    public function testGetStatus()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->agentstatus();

        // cannot test values, uptime will never be consistent.  As such use keys instead
        $keys = array_keys($response);
        sort($keys);

        var_export($keys, true);

        $this->assertEquals([
            'status_period_seconds',
            'status_stored_periods'
        ], $keys);
    }

}
