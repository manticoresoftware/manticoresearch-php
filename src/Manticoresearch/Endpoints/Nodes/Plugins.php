<?php


namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Utils;

class Plugins extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $index;

	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter
	public function setBody($params = null) {
		return parent::setBody(['query' => 'SHOW PLUGINS']);
	}
}
