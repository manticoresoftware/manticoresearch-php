<?php

namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class FlushHostnames extends Sql
{
    use Utils;
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        return parent::setBody(['query' => "FLUSH HOSTNAMES"]);
    }

}