<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\Sql;

class Status extends Sql
{
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        return parent::setBody(['query' => "SHOW STATUS " . (isset($params['pattern']) ? " LIKE '" . $params['pattern'] . "'" : "")]);
    }
}