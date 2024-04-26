<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Query;

use Manticoresearch\Query;

class In extends Query
{
	public function __construct($field, $args) {
		$this->params['in'] = [$field => $args];
	}
}
