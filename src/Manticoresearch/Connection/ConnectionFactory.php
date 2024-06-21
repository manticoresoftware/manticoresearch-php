<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Connection;

use Manticoresearch\Connection;
use Manticoresearch\CurlConnection;

/**
 * Class ConnectionFactory
 * @package Manticoresearch\Connection
 */
class ConnectionFactory
{
	public static function create($config) {
		if (isset($config['transport']) && $config['transport'] === 'PhpHttp') {
			return Connection::create($config);
		} else {
			return CurlConnection::create($config);
		}
	}
}
