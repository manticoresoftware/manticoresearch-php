<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints;

class EmulateBySql extends Sql
{
	public function __construct($params = []) {
		parent::__construct($params);
		$this->setMode('raw');
	}
}
