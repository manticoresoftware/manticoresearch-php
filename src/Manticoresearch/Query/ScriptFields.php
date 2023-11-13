<?php


namespace Manticoresearch\Query;

use Manticoresearch\Query;

class ScriptFields extends Query
{
	private $obj;

	public function __construct() {
		$this->obj = new \stdClass();
	}

	public function add($field, $args = []) {
		$this->obj->$field = [
			'script' => [
				'inline' => $args,
			],

		];
	}

	public function toArray() {
		return $this->convertArray(json_decode(json_encode($this->obj), true));
	}
}
