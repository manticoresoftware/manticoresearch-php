<?php


namespace Manticoresearch\Endpoints\Indices;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;

class Drop extends EmulateBySql
{
    /**
     * @var string
     */
    protected $_index;

    public function setBody( $params = null)
    {
        if(isset( $this->_index)) {
            return parent::setBody(['query' => "DROP TABLE " .
                (isset($params['silent']) && $params['silent']===true?' IF EXISTS ':'').
                $this->_index]);
        }
        throw new RuntimeException('Missing index name in /indices/drop');
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