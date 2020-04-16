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

        // need to remove indexes created by other tests
        $otherIndexes = [
          'testrt', 'products', 'testrtdist', 'testindex', 'movies', 'bulktest'
        ];
        foreach($otherIndexes as $index) {
            $client->indices()->drop([
                    'index' => $index,
                    'body' => ['silent' => true]
                ]
            );
        }


        $helper->populateForKeywords();

        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->tables();
        $this->assertEquals( ['products' => 'rt'],$response);
    }
}
