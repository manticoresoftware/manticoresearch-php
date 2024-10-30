<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Results;

use Manticoresearch\ResultHit;
use Manticoresearch\ResultSet;

class PercolateResultSet extends ResultSet
{
	public function current(): ResultHit {
		return new PercolateResultHit($this->array[$this->position]);
	}
}
