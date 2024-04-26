<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class Sql
 * @package Manticoresearch\Endpoints
 */
class Sql extends Request
{
	/**
	 * @return mixed|string
	 */
	protected $mode;
	public function getPath() {
		return '/sql';
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
	public function getBody() {
		if ($this->mode === 'raw') {
			$return = ['mode=raw'];
			foreach ($this->body as $k => $v) {
				$return[] = $k.'='.urlencode($v);
			}
			return implode('&', $return);
		}

		return http_build_query($this->body);
	}

	public function getMode() {
		return $this->mode;
	}

	public function setMode($mode) {
		$this->mode = $mode;
	}
}
