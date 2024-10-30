<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Connection\Strategy;

use Manticoresearch\Connection;

/**
 * Class Random
 * @package Manticoresearch\Connection\Strategy
 */
class Random implements SelectorInterface
{
	/**
	 * @param array $connections
	 * @return Connection
	 */
	public function getConnection(array $connections):Connection {
		shuffle($connections);
		return $connections[0];
	}
}
