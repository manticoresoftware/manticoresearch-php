<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Exceptions;

use Manticoresearch\Request;

/**
 * Class ConnectionException
 * @package Manticoresearch\Exceptions
 */
class ConnectionException extends \RuntimeException implements ExceptionInterface
{
	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * ConnectionException constructor.
	 * @param string $message
	 * @param Request|null $request
	 */
	public function __construct($message = '', Request $request = null) {
		$this->request = $request;
		parent::__construct($message);
	}

	/**
	 * @return Request|null
	 */
	public function getRequest() {
		return $this->request;
	}

	public function setRequest(Request $request): void {
		$this->request = $request;
	}
}
