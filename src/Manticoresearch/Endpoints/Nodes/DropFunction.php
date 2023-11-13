<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;

class DropFunction extends EmulateBySql
{
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		if (isset($params['name'])) {
			return parent::setBody(['query' => 'DROP FUNCTION ' . $params['name']]);
		}
		throw new RuntimeException('Missing function name in /nodes/dropfunction');
	}
}
