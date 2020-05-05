<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;

class DropPlugin extends EmulateBySql
{
    /**
     * @var string
     */
    protected $index;

    public function setBody($params = null)
    {
        if (isset($params['name'])) {
            return parent::setBody(['query' => "DROP PLUGIN " . $params['name']." TYPE".$params['type']]);
        }
        throw new RuntimeException('Missing plugin name in /nodes/dropplugin');
    }
}
