<?php


namespace Manticoresearch\Results;

use Manticoresearch\ResultSet;

class PercolateResultSet extends ResultSet
{
    public function current(): PercolateResultHit
    {
        return new PercolateResultHit($this->array[$this->position]);
    }
}
