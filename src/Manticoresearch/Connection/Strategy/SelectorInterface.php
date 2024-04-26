<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Connection\Strategy;

use Manticoresearch\Connection;

/**
 * Interface SelectorInterface
 * @package Manticoresearch\Connection\Strategy
 */
interface SelectorInterface
{
	/**
	 * @param array $connections
	 * @return Connection
	 */
	public function getConnection(array $connections):Connection;
}
