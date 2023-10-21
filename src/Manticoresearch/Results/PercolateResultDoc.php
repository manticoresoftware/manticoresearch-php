<?php

namespace Manticoresearch\Results;

use Manticoresearch\ResultHit;

class PercolateResultDoc
{
    protected $doc;


    public function __construct($doc)
    {
        $this->doc = ['doc' => $doc['doc']];
        $this->doc['queries'] = [];
        foreach ($doc['queries'] as $query) {
            $this->doc['queries'][] = new PercolateResultHit($query);
        }
    }

    public function getQueries()
    {
        return $this->doc['queries'];
    }

    public function getData()
    {
        return $this->doc['doc'];
    }

    public function hasQueries()
    {
        return count($this->doc['queries']) > 0;
    }
}
