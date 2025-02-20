<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;

class DropFunction extends EmulateBySql
{
	/**
	 * @var string
	 */
	protected $table;

	public function setBody($params = null) {
		if (isset($params['name'])) {
			return parent::setBody(['query' => 'DROP FUNCTION ' . $params['name']]);
		}
		throw new RuntimeException('Missing function name in /nodes/dropfunction');
	}
}
