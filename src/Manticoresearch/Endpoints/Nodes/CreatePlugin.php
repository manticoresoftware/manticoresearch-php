<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;

class CreatePlugin extends EmulateBySql
{
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		if (isset($params['name'], $params['type']) && $params['library']) {
			return parent::setBody(
				['query' => 'CREATE PLUGIN ' . $params['name'].
				' TYPE '.strtoupper($params['type']). ' SONAME '.$params['library']]
			);
		}

		throw new RuntimeException('Incomplete request for /nodes/createplugin');
	}
}
