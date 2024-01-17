<?php


namespace Manticoresearch\Results;

use Manticoresearch\ResultHit;
use Manticoresearch\ResultSet;

class PercolateResultSet extends ResultSet
{
	public function current(): ResultHit {
		return new PercolateResultHit($this->array[$this->position]);
	}
}
