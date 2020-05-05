<?php
namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Test\Helper\PopulateHelperTest;

class ReloadPluginsTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals(['total'=>0,'error'=>'','warning'=>''], $response);
    }
}
