<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class ReloadPlugins extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		$this->body = $params;
		if (isset($params['library'])) {
			return parent::setBody(['query' => 'RELOAD PLUGINS FROM SONAME '.$params['library']]);
		}
		throw new RuntimeException('library name not present in  /nodes/reloadplugins');
	}
}
