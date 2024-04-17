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
	 * @param string $index
	 * @param int $id
	 */
	public function setPath($index, $id) {
		$path = '/' .  $index . '/_update/' . $id;
		parent::setPath($path);
	}

	/**
	 * @return mixed|string
	 */
	public function getMethod() {
		return 'POST';
	}
}
