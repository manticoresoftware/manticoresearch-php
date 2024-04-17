<?php


namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class PartialReplace
 * @package Manticoresearch\Endpoints
 */
class PartialReplace extends Request
{
	/**
	 * @var string
	 */
	protected $index;

	/**
	 * @return mixed|string
	 */
	public function setPath($index, $id) {
		$this->path = '/' .  $index . '/_update/' . $id;
	}

	/**
	 * @return mixed|string
	 */
	public function getMethod() {
		return 'POST';
	}
}
