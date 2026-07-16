<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch;

class TokenResponse extends Response
{
	public function getResponse() {
		return trim((string)$this->string);
	}

	public function hasError() {
		return $this->status !== null && $this->status >= 400;
	}

	public function getError() {
		if (!$this->hasError()) {
			return '';
		}

		$response = json_decode((string)$this->string, true);
		if (json_last_error() === JSON_ERROR_NONE && isset($response['error'])) {
			return json_encode($response['error'], true);
		}

		return (string)$this->string;
	}
}
