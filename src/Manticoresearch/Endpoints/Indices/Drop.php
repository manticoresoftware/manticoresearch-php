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

    public function setBody()
    {
        if(isset( $this->_index)) {
            return parent::setBody(['query' => "DROP TABLE " . $this->_index]);
        }
        throw new RuntimeException('Missing index name in /indices/drop');
    }
}