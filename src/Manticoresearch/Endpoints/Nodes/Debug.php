<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\Sql;

class Debug extends Sql
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
