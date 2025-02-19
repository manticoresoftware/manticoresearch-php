<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Cluster;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Utils;

/**
 * @todo maybe pattern should be a query parameter rather than body?
 * Class Status
 * @package Manticoresearch\Endpoints\Tables
 */
class Delete extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $cluster;

	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter
	public function setBody($params = null) {
		if (isset($this->cluster)) {
			return parent::setBody(['query' => 'DELETE CLUSTER '.$this->cluster]);
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
