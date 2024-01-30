<?php


namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class Insert
 * @package Manticoresearch\Endpoints
 */
class Insert extends Request
{
	/**
	 * @return mixed|string
	 */
	public function getPath() {
		return '/insert';
	}

	/**
	 * @return mixed|string
	 */
	public function getMethod() {
		return 'POST';
	}
}
