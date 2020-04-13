<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Endpoints\Nodes\Status;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class StatusTest  extends \PHPUnit\Framework\TestCase
{

    public function testGetPath()
    {
        $replace = new Status();
        $this->assertEquals('/sql', $replace->getPath());
    }

    public function testGetMethod()
    {
        $replace = new Status();
        $this->assertEquals('POST', $replace->getMethod());
    }

    public function testGetStatus()
    {
        $helper = new PopulateHelperTest();
        $client = $helper->getClient();
        $response = $client->nodes()->status();

        // cannot test values, uptime will never be consistent.  As such use keys instead
        $keys = array_keys($response);
        sort($keys);

        $this->assertEquals([
            'agent_connect',
            'agent_retry',
            'avg_dist_local',
            'avg_dist_wait',
            'avg_dist_wall',
            'avg_query_cpu',
            'avg_query_readkb',
            'avg_query_reads',
            'avg_query_readtime',
            'avg_query_wall',
            'command_callpq',
            'command_commit',
            'command_delete',
            'command_excerpt',
            'command_flushattrs',
            'command_insert',
            'command_json',
            'command_keywords',
            'command_persist',
            'command_replace',
            'command_search',
            'command_set',
            'command_status',
            'command_suggest',
            'command_update',
            'connections',
            'dist_local',
            'dist_queries',
            'dist_wait',
            'dist_wall',
            'maxed_out',
            'mysql_version',
            'qcache_cached_queries',
            'qcache_hits',
            'qcache_max_bytes',
            'qcache_thresh_msec',
            'qcache_ttl_sec',
            'qcache_used_bytes',
            'queries',
            'query_cpu',
            'query_readkb',
            'query_reads',
            'query_readtime',
            'query_wall',
            'uptime',
            'version',
            'work_queue_length',
            'workers_active',
            'workers_total',

        ], $keys);
    }

}
