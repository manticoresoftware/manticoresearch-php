<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Connection\Strategy;

use Manticoresearch\Connection;

/**
 * Class StaticRoundRobin
 * @package Manticoresearch\Connection\Strategy
 */
class StaticRoundRobin implements SelectorInterface
{
	/**
	 * @var int
	 */
	private $current = 0;

	/**
	 * @param array $connections
	 * @return Connection
	 */
	public function getConnection(array $connections):Connection {
		if (array_key_exists($this->current, $connections) && $connections[$this->current % sizeof($connections)]->isAlive()) {
			return $connections[$this->current];
		}
		++$this->current;
		return $connections[$this->current % sizeof($connections)];
	}
}
