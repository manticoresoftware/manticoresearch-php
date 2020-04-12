<?php

namespace Manticoresearch\Endpoints\Indices;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * Class Create
 * @package Manticoresearch\Endpoints\Indices
 */
class Create extends EmulateBySql
{
    use Utils;
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params = null)
    {
        if (isset($this->_index)) {
            $columns = [];
            if (isset($params['columns'])) {
                foreach ($params['columns'] as $name => $settings) {
                    $column = $name . ' ' . $settings['type'];
                    if (isset($settings['options']) && count($settings['options']) > 0) {
                        $column .= ' ' . implode(' ', $settings['options']);
                    }
                    $columns[] = $column;
                }
            }
            $options = "";
            if (isset($params['settings'])) {
                foreach ($params['settings'] as $name=>$value) {
                    $options.=" ".$name." = '".$value."'";
                }
            }
            return parent::setBody(['query' => "CREATE TABLE ".
                (isset($params['silent']) && $params['silent']===true?' IF NOT EXISTS ':'').
                $this->_index.
                (count($columns)>0?"(".implode(",", $columns).")":" ")
                .$options]);
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
