<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Cluster;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

class Set extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $cluster;

	public function setBody($params = null) {
		if (isset($params['variable'])) {
			return parent::setBody(
				[
				'query' => 'SET CLUSTER' . $this->cluster . " GLOBAL '" . $params['variable']['name'], "'=" .
				(is_numeric($params['variable']['value']) ?
					$params['variable']['value'] : "'" . $params['variable']['value'] . "'"),
				]
			);
		}
		throw new RuntimeException('Variable is missing for /cluster/set');
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
