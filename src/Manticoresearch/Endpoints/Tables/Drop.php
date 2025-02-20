<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Tables;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;

class Drop extends EmulateBySql
{
	/**
	 * @var string
	 */
	protected $table;

	public function setBody($params = null) {
		if (isset($this->table)) {
			return parent::setBody(
				['query' => 'DROP TABLE ' .
				(isset($params['silent']) && $params['silent'] === true ? ' IF EXISTS ' : '').
				$this->table]
			);
		}
		throw new RuntimeException('Missing table name in /indices/drop');
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
