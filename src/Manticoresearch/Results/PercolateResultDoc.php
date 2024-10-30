<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Results;

class PercolateResultDoc
{
	protected $doc;


	public function __construct($doc) {
		$this->doc = ['doc' => $doc['doc']];
		$this->doc['queries'] = [];
		foreach ($doc['queries'] as $query) {
			$this->doc['queries'][] = new PercolateResultHit($query);
		}
	}

	public function getQueries() {
		return $this->doc['queries'];
	}

	public function getData() {
		return $this->doc['doc'];
	}

	public function hasQueries() {
		return sizeof($this->doc['queries']) > 0;
	}
}
