<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class Update
 * @package Manticoresearch\Endpoints
 */
class Update extends Request
{
	/**
	 * @return mixed|string
	 */
	public function getPath() {
		return '/update';
	}

	/**
	 * @return mixed|string
	 */
	public function getMethod() {
		return 'POST';
	}
}
