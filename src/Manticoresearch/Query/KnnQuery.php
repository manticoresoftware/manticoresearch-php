<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Query;

class KnnQuery extends BoolQuery
{
	protected $knnField;
	protected $knnTargetKey;
	protected $knnTarget;
	protected $docCount;

	public function __construct($knnField, $knnTarget, $docCount) {
		$this->knnField = $knnField;
		$this->checkKnnTarget($knnTarget);
		$this->knnTarget = $knnTarget;
		$this->docCount = $docCount;
	}

	protected function checkKnnTarget($knnTarget) {
		if (is_int($knnTarget)) {
			$this->knnTargetKey = 'doc_id';
			return;
		}
		if (is_array($knnTarget)) {
			foreach ($knnTarget as $i) {
				if (!is_numeric($i)) {
					throw new \RuntimeException('KNN query vector must contain numeric values only');
				}
			}
			$this->knnTargetKey = 'query_vector';
			return;
		}
		throw new \RuntimeException("Invalid 'knnTarget' argument passed");
	}

	public function toArray() {
		$paramArr = [
			'field' => $this->knnField,
			$this->knnTargetKey => $this->knnTarget,
			'k' => $this->docCount,
		];
		if ($this->params) {
			$paramArr['filter'] = ['bool' => $this->params];
		}
		return $this->convertArray($paramArr);
	}
}
