<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;

class DropFunction extends Sql
{
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        if(isset($params['name'])) {
            return parent::setBody(['query' => "DROP FUNCTION " . $params['name']]);
        }
        throw new RuntimeException('Missing function name in /nodes/dropfunction');
    }
}
