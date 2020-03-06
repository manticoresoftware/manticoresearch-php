<?php


namespace Manticoresearch\Endpoints;


use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Keywords extends EmulateBySql
{
    use Utils;
    protected $_index;

    public function setBody($params = null)
    {
        if (isset($this->_index)) {
            $binds =[];
            $binds[] = "'" . Utils::escape($params['query']) . "'";
            $binds[] = "'" . $this->_index . "'";
            if (count($params['options']) > 0) {
                foreach ($params['options'] as $name => $value) {
                    $binds[] = "$value AS $name";
                }
            }
            $this->_body = ['query' => "CALL KEYWORDS(" . implode(",", $binds) . ")"];
        }
        throw new RuntimeException('Index name is missing.');

    }
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