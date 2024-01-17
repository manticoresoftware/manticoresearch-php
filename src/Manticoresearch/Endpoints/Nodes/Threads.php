<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;

class Threads extends EmulateBySql
{
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		$options = [];
		if (sizeof($params) > 2) {
			foreach (array_splice($params, 2) as $name => $value) {
				$options[] = "$value=$name";
			}
		}

		return parent::setBody(
			['query' => 'SHOW THREADS ' .
			((sizeof($options) > 0) ? ' OPTION '.implode(',', $options) : '')]
		);
	}
}
