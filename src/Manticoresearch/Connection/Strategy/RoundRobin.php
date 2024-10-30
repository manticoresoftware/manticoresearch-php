<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Connection\Strategy;

use Manticoresearch\Connection;

/**
 * Class RoundRobin
 * @package Manticoresearch\Connection\Strategy
 */
class RoundRobin implements SelectorInterface
{
	/**
	 * @var int
	 */
	private $current = 0;

	/**
	 * @param array $connections
	 * @return Connection
	 */
	public function getConnection(array $connections) :Connection {
		$connection = $connections[$this->current % sizeof($connections)];
		++$this->current;
		return $connection;
	}
}
