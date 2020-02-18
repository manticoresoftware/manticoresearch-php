<?php


namespace Manticoresearch\Endpoints\Indices;


use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * Class Status
 * @package Manticoresearch\Endpoints\Indices
 */
class Status extends Sql
{
    use Utils;
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        if (isset($this->_index)) {
            return parent::setBody(['query' => "SHOW INDEX ".$this->_index. " STATUS".(isset($this->_body['pattern'])?" LIKE '".$params['pattern']."'":"")]);
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