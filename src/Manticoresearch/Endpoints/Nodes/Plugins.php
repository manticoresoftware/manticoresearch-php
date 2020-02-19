<?php


namespace Manticoresearch\Endpoints\Nodes;


use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Utils;

class Plugins extends EmulateBySql
{
    use Utils;
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        return parent::setBody(['query' => "SHOW PLUGINS"]);
    }

}