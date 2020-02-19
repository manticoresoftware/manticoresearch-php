<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;

class Debug extends EmulateBySql
{
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        return parent::setBody(['query' => "DEBUG " . (isset($params['subcommand']) ? $params['subcommand'] : "")]);
    }
}
