<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

class Token extends Request
{
	public function getPath() {
		return '/token';
	}

	public function getMethod() {
		return 'POST';
	}
}
