<?php


namespace Manticoresearch\Endpoints\Indices;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Describe extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		if (isset($this->index)) {
			return parent::setBody(
				['query' => 'DESCRIBE '.$this->index. ' '.
				(isset($params['pattern']) ? " LIKE '".$params['pattern']."'" : '')]
			);
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
