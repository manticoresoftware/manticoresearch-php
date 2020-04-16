<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Endpoints\Nodes\FlushLogs;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class TablesTest  extends \PHPUnit\Framework\TestCase
{
    public function testTables()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $client->indices()->drop([
                'index' => 'testrt',
                'body' => ['silent' => true]
            ]
        );

        $client->indices()->drop([
                'index' => 'products',
                'body' => ['silent' => true]
            ]
        );

        $client->indices()->drop([
                'index' => 'testrtdist',
                'body' => ['silent' => true]
            ]
        );

        $helper->populateForKeywords();

        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->tables();
        $this->assertEquals( ['products' => 'rt'],$response);
    }
}
