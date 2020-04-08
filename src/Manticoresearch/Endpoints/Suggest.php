<?php


namespace Manticoresearch\Endpoints;


use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Suggest extends EmulateBySql
{
    use Utils;
    protected $_index;

    public function __construct($params = [])
    {
        if (isset($params['index'])) {
            $this->setIndex($params['index']);
        }

        parent::__construct($params);
    }

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
            $this->_body = ['query' => "CALL SUGGEST(" . implode(",", $binds) . ")"];
        } else {
            throw new RuntimeException('Index name is missing.');
        }

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
