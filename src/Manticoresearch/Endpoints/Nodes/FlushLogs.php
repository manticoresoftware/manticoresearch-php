<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints\Nodes;

use Manticoresearch\Endpoints\EmulateBySql;
use Manticoresearch\Utils;

class FlushLogs extends EmulateBySql
{
	use Utils;
	/**
	 * @var string
	 */
	protected $index;

	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter
	public function setBody($params = null) {
		return parent::setBody(['query' => 'FLUSH LOGS']);
	}
}
