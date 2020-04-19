<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Test\Helper\PopulateHelperTest;

class ThreadsTest  extends \PHPUnit\Framework\TestCase
{
    public function testThreads()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->threads();

        // there is only one key returned, but it is always a different number
        $index = array_keys($response);
        $response2 = $response[$index[0]];

        // get the keys
        $keys = array_keys($response2);
        $this->assertEquals( [
            'Name',
            'Proto',
            'State',
            'Host',
            'Time',
            'Info'
        ], $keys);
    }

}
