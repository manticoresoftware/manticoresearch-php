<?php


namespace Manticoresearch\Endpoints\Indices;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Alter extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $index;

	public function setBody($params = null) {
		if (isset($this->index)) {
			if (isset($params['operation'])) {
				if ($params['operation'] === 'add' && isset($params['column'])) {
						return parent::setBody(
							['query' => 'ALTER TABLE ' . $this->index . ' ADD COLUMN ' .
							$params['column']['name'] . ' ' . strtoupper($params['column']['type'])]
						);
				}
				if ($params['operation'] === 'drop') {
					return parent::setBody(
						['query' => 'ALTER TABLE ' . $this->index . ' DROP COLUMN ' .
						$params['column']['name']]
					);
				}
				//@todo alter setting, once is merged in master
			}
			throw new RuntimeException('Operation is missing.');
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
