<?php

namespace Manticoresearch\Endpoints\Indices;

use Manticoresearch\Endpoints\Sql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * @todo maybe pattern should be a query parameter rather than body?
 * Class Status
 * @package Manticoresearch\Endpoints\Indices
 */
class Create extends Sql
{
    use Utils;
    /**
     * @var string
     */
    protected $_index;

    public function setBody($params)
    {
        if (isset($this->_index)) {
            $properties = [];
            foreach ($params['properties'] as $name => $settings) {
                $properties[] = $name . ' ' . $settings['type'];
            }
            return parent::setBody(['query' => "CREATE TABLE ".$this->_index."(".implode(",",$properties).")"]);
            return $this;
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