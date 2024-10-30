<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Exceptions;

use Manticoresearch\Request;
use Manticoresearch\Response;

/**
 * Class ResponseException
 * @package Manticoresearch\Exceptions
 */
class ResponseException extends \RuntimeException implements ExceptionInterface
{
	/**
	 * @var Request
	 */
	protected $request;
	/**
	 * @var Response
	 */
	protected $response;

	/**
	 * ResponseException constructor.
	 * @param Request $request
	 * @param Response $response
	 */
	public function __construct(Request $request, Response $response) {
		$this->request = $request;
		$this->response = $response;

		parent::__construct($response->getError());
	}

	/**
	 * @return Request
	 */
	public function getRequest() :Request {
		return $this->request;
	}

	/**
	 * @return Response
	 */
	public function getResponse() :Response {
		return $this->response;
	}
}
