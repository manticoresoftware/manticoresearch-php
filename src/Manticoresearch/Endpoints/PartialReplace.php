<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class PartialReplace
 * @package Manticoresearch\Endpoints
 */
class PartialReplace extends Request
{
	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @param string $table
	 * @param int $id
	 */
	public function setPathByTableAndId($table, $id) {
		$path = '/' .  $table . '/_update/' . $id;
		parent::setPath($path);
	}

	/**
	 * @return mixed|string
	 */
	public function getMethod() {
		return 'POST';
	}
}
