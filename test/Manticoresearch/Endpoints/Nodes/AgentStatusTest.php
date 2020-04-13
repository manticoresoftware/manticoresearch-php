<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class AgentStatusTest  extends \PHPUnit\Framework\TestCase
{

    public function testGetPath()
    {
        $replace = new AgentStatus();
        $this->assertEquals('/sql', $replace->getPath());
    }

    public function testGetMethod()
    {
        $replace = new AgentStatus();
        $this->assertEquals('POST', $replace->getMethod());
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
