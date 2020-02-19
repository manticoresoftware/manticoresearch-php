<?php


namespace Manticoresearch\Endpoints\Indices;


use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Describe extends EmulateBySql
{
    use Utils;
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        if (isset($this->_index)) {
            return parent::setBody(['query' => "DESCRIBE ".$this->_index. " STATUS".(isset($this->_body['pattern'])?" LIKE '".$params['pattern']."'":"")]);

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