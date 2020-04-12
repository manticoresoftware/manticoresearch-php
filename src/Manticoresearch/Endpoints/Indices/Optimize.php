<?php


namespace Manticoresearch\Endpoints\Indices;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * Class Optimize
 * @package Manticoresearch\Endpoints\Indices
 */
class Optimize extends EmulateBySql
{
    use Utils;
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params = null)
    {
        if (isset($this->_index)) {
            return parent::setBody(['query' => "OPTIMIZE INDEX ".$this->_index. "".(isset($params['sync'])?" OPTION sync='".$params['sync']."'":"")]);
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
