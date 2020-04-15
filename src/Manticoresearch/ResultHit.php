<?php


namespace Manticoresearch;

/**
 * Result hit object
 * Element of a result set
 * @category ManticoreSearch
 * @package ManticoreSearch
 * @author Adrian Nuta <adrian.nuta@manticoresearch.com>
 * @link https://manticoresearch.com
 */
class ResultHit
{
    private $_data;

    public function __construct($data = [])
    {
        $this->_data = $data;

    }

    public function getId()
    {
        return $this->_data['_id'];
    }

    public function setId($id)
    {
        $this->_data['id'] = $id;
    }

    public function getScore()
    {
        return $this->_data['_score'];
    }

    public function getHighlight()
    {
        return $this->_data['highlight'];
    }

    public function __get(string $key)
    {
        return $this->get($key);
    }

    public function __isset(string $key): bool
    {
        return $this->has($key) && null !== $this->get($key);
    }

    public function get($key)
    {
        if (isset($this->_data['_source'][$key])) {
            return $this->_data['_source'][$key];
        }
        return [];
    }


    public function has($key)
    {
        return isset($this->data['_source'][$key]);
    }

    public function getData()
    {
        return $this->_data['_source'];
    }

}