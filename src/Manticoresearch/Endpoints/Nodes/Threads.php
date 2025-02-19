<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;

class Threads extends EmulateBySql
{
	/**
	 * @var string
	 */
	protected $table;

	public function setBody($params = null) {
		$options = [];
		if (sizeof($params) > 2) {
			foreach (array_splice($params, 2) as $name => $value) {
				$options[] = "$value=$name";
			}
		}

		return parent::setBody(
			['query' => 'SHOW THREADS ' .
			((sizeof($options) > 0) ? ' OPTION '.implode(',', $options) : '')]
		);
	}
}
