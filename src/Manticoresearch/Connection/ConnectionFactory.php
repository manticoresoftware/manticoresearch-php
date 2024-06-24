<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Connection;

use Manticoresearch\Connection;
use Manticoresearch\CurlConnection;
use \RuntimeException;

/**
 * Class ConnectionFactory
 * @package Manticoresearch\Connection
 */
class ConnectionFactory
{
	public static function create($params) {
		if ($params instanceof Connection) {
			return $params;
		}
		$connectionCls = (is_array($params) && isset($params['transport']) && $params['transport'] === 'PhpHttp')
			? Connection::class
			: CurlConnection::class;
		if (is_array($params)) {
			return new $connectionCls($params);
		}
		throw new RuntimeException('connection must receive array of parameters or self');
	}
}
