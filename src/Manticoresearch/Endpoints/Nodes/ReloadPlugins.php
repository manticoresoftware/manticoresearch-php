<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class ReloadPlugins extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $table;

	public function setBody($params = null) {
		$this->body = $params;
		if (isset($params['library'])) {
			return parent::setBody(['query' => 'RELOAD PLUGINS FROM SONAME '.$params['library']]);
		}
		throw new RuntimeException('library name not present in  /nodes/reloadplugins');
	}
}
