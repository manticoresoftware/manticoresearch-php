<?php


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
