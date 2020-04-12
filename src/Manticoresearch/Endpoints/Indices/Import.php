<?php


namespace Manticoresearch\Endpoints\Indices;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;

class Import extends EmulateBySql
{
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params = null)
    {
        if (isset($this->_index)) {
            if (isset($params['path'])) {
                return parent::setBody([
                    'query' => 'IMPORT TABLE ' .
                        $this->_index .
                        ' FROM ' .
                        $params['path']
                ]);
            }
            throw new RuntimeException('Missing import index path in /indices/import');
        }
        throw new RuntimeException('Missing index name in /indices/import');
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
