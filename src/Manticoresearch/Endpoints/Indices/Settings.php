<?php

namespace Manticoresearch\Endpoints\Indices;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * Class Status
 * @package Manticoresearch\Endpoints\Indices
 */
class Settings extends EmulateBySql
{
    use Utils;
    /**
     * @var string
     */
    protected $index;

    public function setBody($params = null)
    {
        if (isset($this->index)) {
            return parent::setBody(['query' => "SHOW INDEX " . $this->index . " SETTINGS"]);
        }
        throw new RuntimeException('Index name is missing.');
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
