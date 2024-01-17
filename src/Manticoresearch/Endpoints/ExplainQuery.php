<?php


namespace Manticoresearch\Endpoints;

use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class ExplainQuery extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		if (isset($this->index)) {
			if (isset($params['query'])) {
				return parent::setBody(['query' => 'EXPLAIN QUERY '.$this->index. '\''.$params['query'].'\'']);
			}
			throw new RuntimeException('Query param is missing.');
		}
		throw new RuntimeException('Index name is missing.');
	}
	/**
	 * @return mixed
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * @param mixed $index
	 */
	public function setIndex($index) {
		$this->index = $index;
	}
}
