<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Query;

use Manticoresearch\Query;

class Percolate extends Query
{
	public function __construct($docs) {
		$this->params['percolate'] = [];
		if (isset($docs[0]) && (is_array($docs[0]))) {
			$this->params['percolate'] ['documents'] = $docs;
		} else {
			$this->params['percolate'] ['document'] = $docs;
		}
	}
}
