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

    public function setBody($params)
    {
        if(isset($params['name'])) {
            return parent::setBody(['query' => "DROP TABLE " . $this->_index]);
        }
        throw new RuntimeException('Missing index name in /indices/drop');
    }
}