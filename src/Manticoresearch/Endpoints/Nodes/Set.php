<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;

class Set extends EmulateBySql
{
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		$this->body = $params;
		if (isset($params['variable']) && is_array($params['variable'])) {
			return parent::setBody(
				[
				'query' => 'SET ' . (isset($params['type']) ? $params['type'] . "'" : '').' '.
					$params['variable']['name'].'=' . $params['variable']['value'],
				]
			);
		}
		throw new RuntimeException('Variable is missing for /nodes/set');
	}
}
