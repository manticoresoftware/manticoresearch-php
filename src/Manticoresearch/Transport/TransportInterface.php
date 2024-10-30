<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Transport;

use Manticoresearch\Connection;
use Manticoresearch\Exceptions\ExceptionInterface;
use Manticoresearch\Request;
use Manticoresearch\Response;
use Manticoresearch\Transport;

/**
 * Interface TransportInterface
 * @package Manticoresearch\Transport
 */
interface TransportInterface
{
	/**
	 * @param Request $request
	 * @param array $params
	 * @return Response
	 * @throws ExceptionInterface
	 */
	public function execute(Request $request, $params = []);

	/**
	 * @return mixed
	 */
	public function getConnection();

	/**
	 * @param Connection $connection
	 * @return Transport
	 */
	public function setConnection(Connection $connection): Transport;
}
