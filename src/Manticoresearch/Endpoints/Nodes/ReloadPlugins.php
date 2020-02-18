<?php


namespace Manticoresearch\Endpoints\Nodes;


use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * @todo maybe pattern should be a query parameter rather than body?
 * Class Status
 * @package Manticoresearch\Endpoints\Indices
 */
class ReloadPlugins extends Sql
{
    use Utils;
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        if(isset($params['library'])) {
            return parent::setBody(['query' => "RELOAD PLUGINS FROM SONAME ".$params['library']]);
        }
        throw new RuntimeException('library name not present in  /nodes/reloadplugins');
    }

}