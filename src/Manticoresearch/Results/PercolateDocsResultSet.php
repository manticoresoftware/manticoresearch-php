<?php


namespace Manticoresearch\Results;
use Manticoresearch\Response;

class PercolateDocsResultSet implements \Iterator, \Countable
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

    public function __construct($responseObj, $docs)
    {

        foreach ($docs as $doc) {
            $this->array[] = ['doc' => $doc, 'queries' => []];
        }
        $this->response = $responseObj;
        $response = $responseObj->getResponse();
        if (isset($response['hits']['hits'])) {
            $hits = $response['hits']['hits'];
            foreach ($hits as $query) {
                if (isset($query['fields'], $query['fields']['_percolator_document_slot'])) {
                    foreach ($query['fields']['_percolator_document_slot'] as $d) {
                        if (isset($this->array[$d-1])) {
                            $this->array[$d-1]['queries'][] =$query;
                        }

                    }
                }
            }
        }
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return new PercolateResultDoc($this->array[$this->position]);
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
