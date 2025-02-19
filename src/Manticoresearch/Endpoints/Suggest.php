<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints;

use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Suggest extends EmulateBySql
{
	use Utils;
	protected $table;
	public function setBody($params = null) {
		if (isset($this->table)) {
			$binds = [];
			$binds[] = "'" . static::escape($params['query']) . "'";
			$binds[] = "'" . $this->table . "'";
			if (sizeof($params['options']) > 0) {
				foreach ($params['options'] as $name => $value) {
					$binds[] = "$value AS $name";
				}
			}
			return parent::setBody(['query' => 'CALL SUGGEST(' . implode(',', $binds) . ')']);
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
