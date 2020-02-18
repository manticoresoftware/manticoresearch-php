<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\Sql;

class Tables extends Sql
{
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        return parent::setBody(['query' => "SHOW TABLES " . (isset($params['pattern']) ? " LIKE '" . $params['pattern'] . "'" : "")]);
    }
}