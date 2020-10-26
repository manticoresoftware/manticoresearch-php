<?php
namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Endpoints\Nodes\Threads;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class ThreadsTest extends \PHPUnit\Framework\TestCase
{
    public function testThreads()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->threads();

        // there is only one key returned, but it is always a different number
        $index = array_keys($response);
        $response2 = $response[$index[0]];
        $this->assertArrayHasKey('Info', $response2);
    }

    public function testSetBody()
    {
        $threads = new Threads();

        // @todo What are better representative values here
        $threads->setBody(['ignored', 'ignored', 'red', 'yellow']);

        $this->assertEquals('mode=raw&query=SHOW THREADS  OPTION red=0,yellow=1', $threads->getBody());
    }
}
