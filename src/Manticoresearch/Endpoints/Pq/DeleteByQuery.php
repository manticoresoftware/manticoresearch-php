<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Pq;

use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Request;

/**
 * Class DeleteByQuery
 * @package Manticoresearch\Endpoints\Pq
 */
class DeleteByQuery extends Request
{

	/**
	 * @var string
	 */
	protected $table;

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
			return '/pq/' . $this->table . '/_delete_by_query';
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
}
