<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Results;

use Manticoresearch\ResultHit;

class PercolateResultHit extends ResultHit
{
	public function getDocSlots() {
		return $this->data['fields']['_percolator_document_slot'];
	}

	public function getDocsMatched($docs) {
		return array_map(
			function ($v) use ($docs) {
				return $docs[$v - 1];
			}, $this->data['fields']['_percolator_document_slot']
		);
	}
}
