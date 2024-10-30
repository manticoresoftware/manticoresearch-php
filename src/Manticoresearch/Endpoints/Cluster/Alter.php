<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Cluster;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Alter extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $cluster;

	public function setBody($params = null) {
		if (isset($this->cluster)) {
			if (isset($params['operation'])) {
				switch ($params['operation']) {
					case 'add':
						if (isset($params['index'])) {
							return parent::setBody(
								['query' => 'ALTER CLUSTER ' .
								$this->cluster . ' ADD  ' . $params['index']]
							);
						}
						throw new RuntimeException('Index name is missing.');
						break;
					case 'drop':
						if (isset($params['index'])) {
							return parent::setBody(
								['query' => 'ALTER CLUSTER ' .
								$this->cluster . ' DROP  ' . $params['index']]
							);
						}
						throw new RuntimeException('Index name is missing.');
						break;
					case 'update':
						return parent::setBody(['query' => 'ALTER CLUSTER ' .$this->cluster . ' UPDATE nodes']);
						break;
				}
				throw new RuntimeException('Unknown cluster operation');
			}
			throw new RuntimeException('Cluster operation is missing');
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
