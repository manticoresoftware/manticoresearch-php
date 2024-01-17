<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;

class Status extends EmulateBySql
{
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		return parent::setBody(
			['query' => 'SHOW STATUS ' .
			(isset($params['pattern']) ? " LIKE '" . $params['pattern'] . "'" : '')]
		);
	}
}
