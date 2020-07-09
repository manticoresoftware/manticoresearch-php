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
    protected $position = 0;

    /** @var Response */
    protected $response;

    protected $array = [];

    /** @var int|mixed Total number of results */
    protected $total = 0;

    protected $took;

    /** @var mixed Did the query time out? */
    protected $timed_out;

    protected $profile;

    public function __construct($responseObj)
    {
        $this->response = $responseObj;
        $response = $responseObj->getResponse();
        if (isset($response['hits']['hits'])) {
            $this->array = $response['hits']['hits'];
            $this->total = $response['hits']['total'];
        } else {
            $this->total = 0;
        }
        $this->took = $response['took'];
        $this->timed_out = $response['timed_out'];
        if (isset($response['profile'])) {
            $this->profile = $response['profile'];
        }
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return new ResultHit($this->array[$this->position]);
    }

    public function next()
    {
        $this->position++;
    }

    public function valid()
    {
        return isset($this->array[$this->position]);
    }

    public function key()
    {
        return $this->position;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getTime()
    {
        return $this->took;
    }

    public function hasTimedout()
    {
        return $this->timed_out;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function count()
    {
        return count($this->array);
    }

    public function getProfile()
    {
        return $this->profile;
    }
}
