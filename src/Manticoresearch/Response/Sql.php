<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Response;

use Manticoresearch\Response;

class Sql extends Response
{
	public function getResponse() {
		$response = parent::getResponse();
		// workaround for the change in Manticore Search made in 4.2.1, after
		// which any query in mode=raw returns an array
		if (is_array($response)
		and sizeof($response) === 1
		and isset($response[0]['total'], $response[0]['error'], $response[0]['warning'])) {
			foreach ($response[0] as $k => $v) {
				$response[$k] = $v;
			}
			unset($response[0]);
		}
		return $response;
	}
}
