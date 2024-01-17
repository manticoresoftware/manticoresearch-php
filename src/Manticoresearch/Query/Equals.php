<?php


namespace Manticoresearch\Query;

use Manticoresearch\Query;

class Equals extends Query
{
	public function __construct($field, $args) {
		$this->params['equals'] = [$field => $args];
	}
}
