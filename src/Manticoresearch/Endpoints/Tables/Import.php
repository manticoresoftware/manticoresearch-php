<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Tables;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;

class Import extends EmulateBySql
{
	/**
	 * @var string
	 */
	protected $table;

	public function setBody($params = null) {
		if (isset($this->table)) {
			if (isset($params['path'])) {
				return parent::setBody(
					[
					'query' => 'IMPORT TABLE ' .
						$this->table .
						' FROM ' .
						$params['path'],
					]
				);
			}
			throw new RuntimeException('Missing import table path in /indices/import');
		}
		throw new RuntimeException('Missing table name in /indices/import');
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
