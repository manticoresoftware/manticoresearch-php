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
	protected $table;
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
		if (isset($this->table)) {
			if (isset($this->id)) {
				return '/pq/' . $this->table . '/doc/' . $this->id;
			}

			return '/pq/' . $this->table . '/doc';
		}
		throw new RuntimeException('Table name is missing.');
	}

	/**
	 * @return mixed
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * @param mixed $table
	 */
	public function setTable($table) {
		$this->table = $table;
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
