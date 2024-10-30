<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Indices;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * Class Create
 * @package Manticoresearch\Endpoints\Indices
 */
class Create extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		if (isset($this->index)) {
			$columns = [];
			if (isset($params['columns'])) {
				foreach ($params['columns'] as $name => $settings) {
					$column = '`' . $name . '` ' . $settings['type'];
					if (isset($settings['options']) && sizeof($settings['options']) > 0) {
						$column .= ' ' . implode(' ', $settings['options']);
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
				$this->index.
				(sizeof($columns) > 0 ? '('.implode(',', $columns).')' : ' ')
				.$options]
			);
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
}
