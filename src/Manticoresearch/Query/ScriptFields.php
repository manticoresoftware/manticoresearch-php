<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Query;

use Manticoresearch\Query;

class ScriptFields extends Query
{
	private $obj;

	public function __construct() {
		$this->obj = new \stdClass();
	}

	public function add($field, $args = []) {
		$this->obj->$field = [
			'script' => [
				'inline' => $args,
			],

		];
	}

	public function toArray() {
		return $this->convertArray(json_decode(json_encode($this->obj), true));
	}
}
