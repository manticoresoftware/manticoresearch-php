<?php


namespace Manticoresearch\Results;

use Manticoresearch\ResultSet;
use Manticoresearch\ResultHit;


class PercolateResultSet extends ResultSet
{
    public function current(): ResultHit
    {
        return new PercolateResultHit($this->array[$this->position]);
    }
}
