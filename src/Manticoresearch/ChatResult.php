<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch;

class ChatResult
{
	protected $responseObject;
	protected $data;

	public function __construct(Response $responseObject) {
		$this->responseObject = $responseObject;
		$this->data = $responseObject->getResponse();
	}

	public function getConversationUuid() {
		return $this->data['conversation_uuid'] ?? null;
	}

	public function getUserQuery() {
		return $this->data['user_query'] ?? null;
	}

	public function getSearchQuery() {
		return $this->data['search_query'] ?? null;
	}

	public function getResponse() {
		return $this->data['response'] ?? null;
	}

	public function getAnswer() {
		return $this->getResponse();
	}

	public function getSources() {
		$sources = $this->data['sources'] ?? [];
		if (is_array($sources)) {
			return $sources;
		}

		$decoded = json_decode($sources, true);
		return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
	}

	public function getRawSources() {
		return $this->data['sources'] ?? null;
	}

	public function getData() {
		return $this->data;
	}

	public function getResponseObject() {
		return $this->responseObject;
	}
}
