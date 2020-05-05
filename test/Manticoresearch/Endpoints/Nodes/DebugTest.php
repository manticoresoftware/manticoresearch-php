<?php
namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Test\Helper\PopulateHelperTest;

class DebugTest extends \PHPUnit\Framework\TestCase
{

    public function testDebug()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->debug(['body' => []]);

        // cannot test values, uptime will never be consistent.  As such use keys instead
        $keys = array_keys($response);
        sort($keys);

        $this->assertEquals([
            'flush logs',
            'malloc_stats',
            'malloc_trim',
            'reload indexes',
            'sched',
            'sleep Nsec',
            'systhreads',
            'tasks',
        ], $keys);
    }
}
