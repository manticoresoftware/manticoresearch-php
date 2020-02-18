<?php


namespace Manticoresearch\Endpoints\Indices;


use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * Class Optimize
 * @package Manticoresearch\Endpoints\Indices
 */
class Truncate extends Sql
{
    use Utils;
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        if (isset($this->_index)) {
            return parent::setBody(['query' => "TRUNCATE RTINDEX ".$this->_index. "".(isset($this->_body['with'])?" WITH'".strtoupper($params['with'])."'":"")]);
        }
        throw new RuntimeException('Index name is missing.');
    }
    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->_index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->_index = $index;
    }

}