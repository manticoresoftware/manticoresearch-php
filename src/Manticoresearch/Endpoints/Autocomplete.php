<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class Autocomplete
 * @package Manticoresearch\Endpoints
 */
class Autocomplete extends Request
{
	/**
	 * @return mixed|string
	 */
	public function getPath() {
		return '/autocomplete';
	}

	/**
	 * @return mixed|string
	 */
	public function getMethod() {
		return 'POST';
	}

	/**
	 * @return mixed|string
	 */
	public function getContentType() {
		return 'application/json';
	}

	/**
	 * @param mixed $body
	 */
	public function setBody($body = null) {
		if (is_array($body)) {
			$this->body = json_encode($body, true);
		} else {
			$this->body = $body;
		}
	}

}
