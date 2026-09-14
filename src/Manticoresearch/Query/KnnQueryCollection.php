<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Query;

class KnnQueryCollection extends BoolQuery
{
	protected $queries;

	public function __construct($queries) {
		$this->queries = $queries;
	}

	public function toArray() {
		return array_map(
			function (KnnQuery $query) {
				return $query->toRrfArray();
			},
			$this->queries
		);
	}

	public function toRrfArray() {
		return $this->toArray();
	}

	public function getFilterQuery() {
		if (!$this->params) {
			return null;
		}

		return $this->convertArray(['bool' => $this->params]);
	}
}
