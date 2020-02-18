<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;

class CreatePlugin extends Sql
{
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        if(iseet($params['name']) && isset($params['type']) && $params['library']) {
            return parent::setBody(['query' => "CREATE PLUGIN " . $params['name']." TYPE ".strtoupper($params['type']). " SONAME ".$params['library']]);
        }

        throw new RuntimeException('Incomplete request for /nodes/createplugin');
    }
}