<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;

class Tables extends EmulateBySql
{
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		return parent::setBody(
			['query' => 'SHOW TABLES ' .
			(isset($params['pattern']) ? " LIKE '" . $params['pattern'] . "'" : '')]
		);
	}
}
