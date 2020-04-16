<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Endpoints\Nodes\FlushLogs;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class SetTest  extends \PHPUnit\Framework\TestCase
{

    /**
     * See https://docs.manticoresearch.com/latest/html/sphinxql_reference/set_syntax.html
     */
    public function testSet()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $payload = [
            'body' => [
                'variable' => [
                    'name' => 'PROFILING',
                    'value' => 0
                ]
            ]
        ];
        $response = $client->nodes()->set($payload);
        $this->assertEquals( ['total'=>0,'error'=>'','warning'=>''],$response);    }
}
