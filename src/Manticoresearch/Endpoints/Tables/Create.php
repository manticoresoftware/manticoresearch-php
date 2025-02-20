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
 * Class Create
 * @package Manticoresearch\Endpoints\Tables
 */
class Create extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $table;

	protected function buildOptionsExpr($options) {
		$exprParts = [];
		foreach ($options as $k => $v) {
			$exprParts[] = is_string($k) ? "$k='$v'" : $v;
		}

		return ' ' . implode(' ', $exprParts);
	}

	public function setBody($params = null) {
		if (isset($this->table)) {
			$columns = [];
			if (isset($params['columns'])) {
				foreach ($params['columns'] as $name => $settings) {
					$column = '`' . $name . '` ' . $settings['type'];
					if (isset($settings['options']) && sizeof($settings['options']) > 0) {
						$column .= $this->buildOptionsExpr($settings['options']);
					}
					$columns[] = $column;
				}
			}
			$options = '';
			if (isset($params['settings'])) {
				foreach ($params['settings'] as $name => $value) {
					if (is_array($value)) {
						foreach ($value as $v) {
							$options .= ' '.$name." = '".$v."'";
						}
					} else {
						$options .= ' '.$name." = '".$value."'";
					}
				}
			}

			return parent::setBody(
				['query' => 'CREATE TABLE '.
				(isset($params['silent']) && $params['silent'] === true ? ' IF NOT EXISTS ' : '').
				$this->table.
				(sizeof($columns) > 0 ? '('.implode(',', $columns).')' : ' ')
				.$options]
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
