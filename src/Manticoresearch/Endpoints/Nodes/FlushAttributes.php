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
class FlushAttributes extends Sql
{
    use Utils;
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {

        return parent::setBody(['query' => "FLUSH ATTRIBUTES"]);

    }


}