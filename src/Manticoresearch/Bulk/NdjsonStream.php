<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Bulk;

use Manticoresearch\Exceptions\RuntimeException;

/**
 * Incremental NDJSON body for one /bulk?indexer_rt_bulk=1 request.
 *
 * Encodes documents on demand into a small leftover buffer — never builds the
 * full request body as a single string.
 *
 * When $maxDocs is 0, the stream keeps pulling from the shared source until EOF
 * (or a table change).
 */
class NdjsonStream
{
	/** @var OperationIterator */
	private $source;

	/** @var int 0 = unlimited */
	private $maxDocs;

	/** @var string */
	private $buffer = '';

	/** @var int */
	private $docs = 0;

	/** @var int */
	private $bytes = 0;

	/** @var array<int,bool> */
	private $ids = [];

	/** @var string|null */
	private $table;

	/** @var bool */
	private $closed = false;

	/** @var bool */
	private $empty = true;

	/**
	 * @param OperationIterator $source
	 * @param int $maxDocs 0 = pull until source EOF
	 */
	public function __construct(OperationIterator $source, int $maxDocs = 0) {
		$this->source = $source;
		$this->maxDocs = max(0, $maxDocs);
		$this->prime();
	}

	/**
	 * Create a stream only when at least one document is available.
	 *
	 * @param OperationIterator $source
	 * @param int $maxDocs
	 * @return self|null
	 */
	public static function tryCreate(OperationIterator $source, int $maxDocs = 0) {
		$stream = new self($source, $maxDocs);
		if ($stream->isEmpty()) {
			return null;
		}
		return $stream;
	}

	/**
	 * @return bool
	 */
	public function isEmpty(): bool {
		return $this->empty;
	}

	/**
	 * @return int
	 */
	public function getDocs(): int {
		return $this->docs;
	}

	/**
	 * @return int
	 */
	public function getBytes(): int {
		return $this->bytes;
	}

	/**
	 * @return string|null
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * Pull up to $size encoded bytes for curl's READFUNCTION.
	 *
	 * @param int $size
	 * @return string Empty string signals EOF for this request
	 */
	public function read(int $size): string {
		if ($size <= 0) {
			return '';
		}
		while (strlen($this->buffer) < $size && !$this->closed) {
			if (!$this->appendNextLine()) {
				$this->closed = true;
				break;
			}
		}
		if ($this->buffer === '') {
			return '';
		}
		$chunk = substr($this->buffer, 0, $size);
		$this->buffer = substr($this->buffer, strlen($chunk));
		return $chunk;
	}

	/**
	 * @return void
	 */
	private function prime(): void {
		if ($this->appendNextLine()) {
			$this->empty = false;
		} else {
			$this->closed = true;
		}
	}

	/**
	 * @return bool true when a line was appended
	 */
	private function appendNextLine(): bool {
		if ($this->maxDocs > 0 && $this->docs >= $this->maxDocs) {
			return false;
		}
		$payload = $this->source->next();
		if ($payload === null) {
			return false;
		}

		if ($this->table !== null && $payload['table'] !== $this->table) {
			$this->source->unget($payload);
			return false;
		}

		if (isset($this->ids[$payload['id']])) {
			throw new RuntimeException(
				'Duplicate document id ' . $payload['id'] . ' within the same bulk request'
			);
		}

		$line = $this->source->getNormalizer()->encodeLine($payload) . "\n";
		$this->table = $payload['table'];
		$this->ids[$payload['id']] = true;
		$this->buffer .= $line;
		$this->bytes += strlen($line);
		$this->docs++;
		return true;
	}
}
