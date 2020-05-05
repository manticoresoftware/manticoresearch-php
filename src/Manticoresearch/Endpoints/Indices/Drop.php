<?php


namespace Manticoresearch\Endpoints\Indices;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;

class Drop extends EmulateBySql
{
    /**
     * @var string
     */
    protected $index;

    public function setBody($params = null)
    {
        if (isset($this->index)) {
            return parent::setBody(['query' => "DROP TABLE " .
                (isset($params['silent']) && $params['silent']===true?' IF EXISTS ':'').
                $this->index]);
        }
        throw new RuntimeException('Missing index name in /indices/drop');
    }
    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }
}
