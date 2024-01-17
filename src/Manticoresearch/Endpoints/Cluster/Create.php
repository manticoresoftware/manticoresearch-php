<?php

namespace Manticoresearch\Endpoints\Cluster;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * @todo maybe pattern should be a query parameter rather than body?
 * Class Status
 * @package Manticoresearch\Endpoints\Indices
 */
class Create extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $cluster;

	public function setBody($params = null) {
		if (isset($this->cluster)) {
			$options = [];
			if (isset($params['path'])) {
				$options[] = "'" . $params['path'] . "' AS path";
			}
			if (isset($params['nodes'])) {
				$options[] = "'" . $params['nodes'] . "' AS nodes";
			}
			return parent::setBody(
				['query' => 'CREATE CLUSTER ' . $this->cluster .
				((sizeof($options) > 0) ? ' ' . implode(',', $options) : '')]
			);
		}
		throw new RuntimeException('Cluster name is missing.');
	}

	/**
	 * @return mixed
	 */
	public function getCLuster() {
		return $this->cluster;
	}

	/**
	 * @param mixed $cluster
	 */
	public function setCluster($cluster) {
		$this->cluster = $cluster;
	}
}
