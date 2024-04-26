<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Pq;

use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Request;

/**
 * Class Search
 * @package Manticoresearch\Endpoints\Pq
 */
class Search extends Request
{

	/**
	 * @var string
	 */
	protected $index;

	/**
	 * @return mixed|string
	 */
	public function getMethod() {
		return 'POST';
	}

	/**
	 * @return mixed|string
	 */
	public function getPath() {
		if (isset($this->index)) {
			return '/pq/' . $this->index . '/search';
		}
		throw new RuntimeException('Index name is missing.');
	}

	/**
	 * @return mixed
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * @param mixed $index
	 */
	public function setIndex($index) {
		$this->index = $index;
	}
}
