<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class Replace
 * @package Manticoresearch\Endpoints
 */
class Replace extends Request
{
	/**
	 * @return mixed|string
	 */
	public function getPath() {
		return '/replace';
	}

	/**
	 * @return mixed|string
	 */
	public function getMethod() {
		return 'POST';
	}
}
