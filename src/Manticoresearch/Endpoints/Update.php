<?php


namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class Update
 * @package Manticoresearch\Endpoints
 */
class Update extends Request
{
	/**
	 * @return mixed|string
	 */
	public function getPath() {
		return '/update';
	}

	/**
	 * @return mixed|string
	 */
	public function getMethod() {
		return 'POST';
	}
}
