<?php


namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class Replace
 * @package Manticoresearch\Endpoints
 */
class Replace extends Request
{
	/**
	 * @return mixed|string
	 */
	public function getPath() {
		return '/json/replace';
	}

	/**
	 * @return mixed|string
	 */
	public function getMethod() {
		return 'POST';
	}
}
