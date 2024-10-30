<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Pq;

use Manticoresearch\Exceptions\RuntimeException;

/**
 * Class Doc
 * @package Manticoresearch\Endpoints\Pq
 */
class Doc extends \Manticoresearch\Request
{
	/**
	 * @var string
	 */
	protected $index;
	/**
	 * @var integer
	 */
	protected $id;

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
			if (isset($this->id)) {
				return '/pq/' . $this->index . '/doc/' . $this->id;
			}

			return '/pq/' . $this->index . '/doc';
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

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
}
