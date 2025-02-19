<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Tables;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Alter extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $table;

	public function setBody($params = null) {
		if (isset($this->table)) {
			if (isset($params['operation'])) {
				if ($params['operation'] === 'add' && isset($params['column'])) {
						return parent::setBody(
							['query' => 'ALTER TABLE ' . $this->table . ' ADD COLUMN ' .
							$params['column']['name'] . ' ' . strtoupper($params['column']['type'])]
						);
				}
				if ($params['operation'] === 'drop') {
					return parent::setBody(
						['query' => 'ALTER TABLE ' . $this->table . ' DROP COLUMN ' .
						$params['column']['name']]
					);
				}
				//@todo alter setting, once is merged in master
			}
			throw new RuntimeException('Operation is missing.');
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
