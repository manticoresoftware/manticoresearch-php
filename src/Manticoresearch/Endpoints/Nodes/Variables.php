<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;

class Variables extends EmulateBySql
{
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		$option = '';
		if (isset($params['pattern'])) {
			$option = "LIKE '".$params['pattern']."'";
		}
		if (isset($params['where'])) {
			$option = "WHERE variable_name='".$params['where']['variable_name']."'";
		}
		return parent::setBody(['query' => 'SHOW '.($params['type'] ?? '').' VARIABLES '.$option]);
	}
}
