<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Response;

use Manticoresearch\Response;

class Token extends Response
{
	public function getResponse() {
		$raw = trim((string)$this->string);
		if ($raw === '') {
			return $raw;
		}

		$decoded = json_decode($raw, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return $raw;
		}

		if (is_string($decoded)) {
			return $decoded;
		}

		if (is_array($decoded) && isset($decoded['token']) && is_string($decoded['token'])
			&& $decoded['token'] !== '') {
			return $decoded['token'];
		}

		return $raw;
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
			return json_encode($response['error']);
		}

		return (string)$this->string;
	}
}
