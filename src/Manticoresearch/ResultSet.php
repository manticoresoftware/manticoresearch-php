<?php


namespace Manticoresearch;

/**
 * Manticore result set
 *  List hits returned by a search
 *  Implements iterator and countable
 * @category ManticoreSearch
 * @package ManticoreSearch
 * @author Adrian Nuta <adrian.nuta@manticoresearch.com>
 * @link https://manticoresearch.com
 * @see \Iterator
 */
class ResultSet implements \Iterator, \Countable
{
    /** @var int The position of the iterator through the result set */
    private $_position = 0;

    /** @var Response */
    private $_response;

    private $_array = [];

    /** @var int|mixed Total number of results */
    private $_total = 0;

    private $_took;

    /** @var mixed Did the query time out? */
    private $_timed_out;

    private $_profile;

    public function __construct($responseObj)
    {
        $this->_response = $responseObj;
        $response = $responseObj->getResponse();
        if (isset($response['hits']['hits'])) {
            $this->_array = $response['hits']['hits'];
            $this->_total = $response['hits']['total'];
        } else {
            $this->_total = 0;
        }
        $this->_took = $response['took'];
        $this->_timed_out = $response['timed_out'];
        if (isset($response['profile'])) {
            $this->_profile = $response['profile'];
        }

    }

    public function rewind()
    {
        $this->_position = 0;
    }

    public function current()
    {
        return new ResultHit($this->_array[$this->_position]);
    }

    public function next()
    {
        $this->_position++;
    }

    public function valid()
    {
        return isset($this->_array[$this->_position]);
    }

    public function key()
    {
        return $this->_position;
    }

    public function getTotal()
    {
        return $this->_total;
    }

    public function getTime()
    {
        return $this->_took;
    }

    public function hasTimedout()
    {
        return $this->_timed_out;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    public function count()
    {
        return count($this->_array);
    }

    public function getProfile()
    {
        return $this->_profile;
    }

}
