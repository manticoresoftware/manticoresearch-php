<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Bulk;

/**
 * Single-pass cursor over bulk operations with one-slot pushback.
 *
 * Concurrent NdjsonStream readers share one cursor; curl_multi invokes
 * READFUNCTION callbacks on the same PHP thread, so no locking is required.
 */
class OperationIterator
{
	/** @var OperationNormalizer */
	private $normalizer;

	/** @var \Iterator */
	private $inner;

	/** @var array|null */
	private $pending;

	/** @var bool */
	private $started = false;

	/**
	 * @param iterable $operations
	 * @param OperationNormalizer $normalizer
	 */
	public function __construct(iterable $operations, OperationNormalizer $normalizer) {
		$this->normalizer = $normalizer;
		if ($operations instanceof \Iterator) {
			$this->inner = $operations;
		} elseif ($operations instanceof \Traversable) {
			$this->inner = new \IteratorIterator($operations);
		} else {
			$this->inner = new \ArrayIterator($operations);
		}
	}

	/**
	 * @return OperationNormalizer
	 */
	public function getNormalizer(): OperationNormalizer {
		return $this->normalizer;
	}

	/**
	 * @return array|null Normalized insert payload or null at end of input
	 */
	public function next() {
		if ($this->pending !== null) {
			$payload = $this->pending;
			$this->pending = null;
			return $payload;
		}
		if (!$this->started) {
			$this->started = true;
			$this->inner->rewind();
		} else {
			$this->inner->next();
		}
		if (!$this->inner->valid()) {
			return null;
		}
		return $this->normalizer->normalize($this->inner->current());
	}

	/**
	 * Push one payload back so another stream / the same stream boundary can consume it.
	 *
	 * @param array $payload
	 * @return void
	 */
	public function unget(array $payload): void {
		if ($this->pending !== null) {
			throw new \LogicException('OperationIterator only supports one pushback slot');
		}
		$this->pending = $payload;
	}
}
