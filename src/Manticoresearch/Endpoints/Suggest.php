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

	const ALLOWED_OPTIONS = [
		'limit',
		'max_edits',
		'result_stats',
		'delta_len',
		'max_matches',
		'reject',
		'result_line',
		'non_char',
		'sentence',
	];
	
	protected $table;

	public function setBody($params = null) {
		if (isset($this->table)) {
			$binds = [];
			$binds[] = "'" . static::escape($params['query']) . "'";
			$binds[] = "'" . $this->table . "'";
			if (sizeof($params['options']) > 0) {
				$opts = array_filter(
					$params['options'],
					function ($name) {
						return in_array($name, static::ALLOWED_OPTIONS);
					},
					ARRAY_FILTER_USE_KEY
				);
				foreach ($opts as $name => $value) {
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
