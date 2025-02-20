<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Tables;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * Class Status
 * @package Manticoresearch\Endpoints\Tables
 */
class Status extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $table;

	public function setBody($params = null) {
		if (isset($this->table)) {
			return parent::setBody(
				['query' => 'SHOW TABLE '.$this->table. ' STATUS'.
				(isset($params['pattern']) ? " LIKE '".$params['pattern']."'" : '')]
			);
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
