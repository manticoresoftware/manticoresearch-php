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
    protected $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function getId()
    {
        return $this->data['_id'];
    }

    public function setId($id)
    {
        $this->data['_id'] = $id;
    }

    public function getScore()
    {
        return $this->data['_score'];
    }

    public function getHighlight()
    {
        return $this->data['highlight'];
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
        if (isset($this->data['_source'][$key])) {
            return $this->data['_source'][$key];
        }
        return [];
    }


    public function has($key)
    {
        return isset($this->_data['_source'][$key]);
    }

    public function getData()
    {
        return $this->data['_source'];
    }
}
