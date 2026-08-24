<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints;

use Manticoresearch\Request;

/**
 * Class Bulk
 * @package Manticoresearch\Endpoints
 */
class Bulk extends Request
{
	/**
	 * @return mixed|string
	 */
	public function getPath() {
		return '/bulk';
	}

	/**
	 * @return mixed|string
	 */
	public function getMethod() {
		return 'POST';
	}

	/**
	 * @return mixed|string
	 */
	public function getContentType() {
		return 'application/x-ndjson';
	}

	/**
	 * @param mixed $body
	 */
	public function setBody($body = null) {
		if (is_array($body) || $body instanceof \Traversable) {
			$this->body = '';
			foreach ($body as $b) {
				$this->body .= json_encode($b, true) . "\n";
			}
		} else {
			$this->body = $body;
		}
	}

	/**
	 * Enable indexer-assisted RT bulk mode via /bulk?indexer_rt_bulk=1.
	 *
	 * @param bool $enabled
	 * @return $this
	 */
	public function setIndexerRtBulk(bool $enabled = true) {
		$query = $this->getQuery();
		if (!is_array($query)) {
			$query = [];
		}
		if ($enabled) {
			$query['indexer_rt_bulk'] = 1;
		} else {
			unset($query['indexer_rt_bulk']);
		}
		$this->setQuery($query);
		return $this;
	}
}
