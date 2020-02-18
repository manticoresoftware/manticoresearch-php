<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;

class CreateFunction extends Sql
{
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        if (iseet($params['name']) && isset($params['type']) && $params['library']) {
            return parent::setBody(['query' => "CREATE FUNCTION " . $params['name'] . " RETURNS " . strtoupper($params['type']) . " SONAME " . $params['library']]);
        }
        throw new RuntimeException('Incomplete request for /nodes/createplugin');
    }
}