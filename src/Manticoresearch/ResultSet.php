<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch;

/**
 * Manticore result set
 *  List hits returned by a search
 *  Implements iterator and countable
 * @category ManticoreSearch
 * @package ManticoreSearch
 * @author Adrian Nuta <adrian.nuta@manticoresearch.com>
 * @link https://manticoresearch.com
 * @see \Iterator
 */
class ResultSet implements \Iterator, \Countable
{
	/** @var int The position of the iterator through the result set */
	protected $position = 0;

	/** @var Response */
	protected $response;

	protected $array = [];

	/** @var int|mixed Total number of results */
	protected $total = 0;

	protected $took;

	/** @var mixed Did the query time out? */
	protected $timed_out; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

	protected $profile;

	protected $facets;

	protected $scroll;

	public function __construct($responseObj) {
		$this->response = $responseObj;
		$response = $responseObj->getResponse();
		if (isset($response['hits']['hits'])) {
			$this->array = array_values($response['hits']['hits']);
			$this->total = $response['hits']['total'];
		} else {
			$this->total = 0;
		}
		$this->scroll = $response['scroll'] ?? 0;
		$this->took = $response['took'] ?? 0;
		// phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		$this->timed_out = $response['timed_out'] ?? false;
		if (isset($response['profile'])) {
			$this->profile = $response['profile'];
		}
		if (!isset($response['aggregations'])) {
			return;
		}

		$this->facets = $response['aggregations'];
	}

	public function rewind(): void {
		$this->position = 0;
	}

	public function current(): ResultHit {
		return new ResultHit($this->array[$this->position]);
	}

	public function next(): void {
		$this->position++;
	}

	public function valid(): bool {
		return isset($this->array[$this->position]);
	}

	public function key(): int {
		return $this->position;
	}

	public function getScroll() {
		return $this->scroll;
	}

	public function getTotal() {
		return $this->total;
	}

	public function getTime() {
		return $this->took;
	}

	public function hasTimedout() {
		return $this->timed_out; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
	}

	/**
	 * @return Response
	 */
	public function getResponse() {
		return $this->response;
	}

	public function count(): int {
		return sizeof($this->array);
	}

	public function getProfile() {
		return $this->profile;
	}

	public function getFacets() {
		return $this->facets;
	}
}
