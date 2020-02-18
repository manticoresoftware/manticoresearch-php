<?php


namespace Manticoresearch\Endpoints\Indices;

use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;

class Drop extends Sql
{
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        if(isset($params['name'])) {
            return parent::setBody(['query' => "DROP TABLE " . $this->_index]);
        }
        throw new RuntimeException('Missing index name in /indices/drop');
    }
}