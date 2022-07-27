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

    protected $facets;

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
        if (isset($response['aggregations'])) {
            $this->facets = $response['aggregations'];
        }
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): ResultHit
    {
        return new ResultHit($this->array[$this->position]);
    }

    public function next(): void
    {
        $this->position++;
    }

    public function valid(): bool
    {
        return isset($this->array[$this->position]);
    }

    public function key(): mixed
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

    public function count(): int
    {
        return count($this->array);
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function getFacets()
    {
        return $this->facets;
    }
}
