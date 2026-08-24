<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Bulk;

use Manticoresearch\Exceptions\RuntimeException;

/**
 * Validates and normalizes insert operations for indexer-assisted bulk mode.
 */
class OperationNormalizer
{
	/** @var string|null */
	private $expectedTable;

	/** @var bool */
	private $rejectCluster;

	/**
	 * @param string|null $expectedTable When set, every operation must target this table
	 * @param bool $rejectCluster Reject operations that include a cluster field
	 */
	public function __construct(?string $expectedTable = null, bool $rejectCluster = true) {
		$this->expectedTable = $expectedTable;
		$this->rejectCluster = $rejectCluster;
	}

	/**
	 * Normalize one bulk action into an insert payload array.
	 *
	 * Accepts either:
	 * - ['insert' => ['table' => ..., 'id' => ..., 'doc' => ...]]
	 * - ['table' => ..., 'id' => ..., 'doc' => ...]
	 * - document arrays with an id key (when expectedTable is set)
	 *
	 * @param mixed $operation
	 * @return array{table:string,id:int,doc:array}
	 */
	public function normalize($operation): array {
		if (is_object($operation)) {
			$operation = (array)$operation;
		} elseif (is_string($operation)) {
			$decoded = json_decode($operation, true);
			if (!is_array($decoded)) {
				throw new RuntimeException('Invalid JSON bulk operation');
			}
			$operation = $decoded;
		}
		if (!is_array($operation)) {
			throw new RuntimeException('Bulk operation must be an array or JSON object');
		}

		if (isset($operation['insert'])) {
			if (!is_array($operation['insert'])) {
				throw new RuntimeException('insert action payload must be an array');
			}
			$payload = $operation['insert'];
		} elseif ($this->isActionEnvelope($operation)) {
			$actions = array_keys($operation);
			throw new RuntimeException(
				'indexer-assisted bulk supports insert only; got action: ' . $actions[0]
			);
		} else {
			$payload = $operation;
		}

		return $this->normalizePayload($payload);
	}

	/**
	 * Encode a normalized insert payload as one NDJSON line (without trailing newline).
	 *
	 * @param array $payload
	 * @return string
	 */
	public function encodeLine(array $payload): string {
		$line = json_encode(
			['insert' => $payload],
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION
		);
		if ($line === false) {
			throw new RuntimeException('Failed to JSON-encode bulk insert: ' . json_last_error_msg());
		}
		return $line;
	}

	/**
	 * @param array $payload
	 * @return array{table:string,id:int,doc:array}
	 */
	private function normalizePayload(array $payload): array {
		if ($this->rejectCluster && isset($payload['cluster'])) {
			throw new RuntimeException(
				'indexer-assisted bulk does not support clustered/replicated tables'
			);
		}

		$table = $payload['table'] ?? $payload['index'] ?? $this->expectedTable;
		if ($table === null || $table === '') {
			throw new RuntimeException('Bulk insert requires a table name');
		}
		$table = (string)$table;
		if ($this->expectedTable !== null && $table !== $this->expectedTable) {
			throw new RuntimeException(
				'indexer-assisted bulk transaction may target only one table; expected '
				. $this->expectedTable . ', got ' . $table
			);
		}

		if (!array_key_exists('id', $payload)) {
			throw new RuntimeException(
				'indexer-assisted bulk requires an explicit non-zero numeric document id'
			);
		}
		$id = $payload['id'];
		if (is_string($id) && !is_numeric($id)) {
			throw new RuntimeException('Incorrect document id passed');
		}
		$id = (int)$id;
		if ($id === 0) {
			throw new RuntimeException(
				'indexer-assisted bulk requires an explicit non-zero numeric document id'
			);
		}

		if (isset($payload['doc'])) {
			if (!is_array($payload['doc'])) {
				throw new RuntimeException('insert doc must be an array');
			}
			$doc = $payload['doc'];
		} else {
			$doc = $payload;
			unset($doc['id'], $doc['table'], $doc['index'], $doc['cluster']);
		}
		$this->rejectNulls($doc);

		return [
			'table' => $table,
			'id' => $id,
			'doc' => $doc,
		];
	}

	/**
	 * @param array $operation
	 * @return bool
	 */
	private function isActionEnvelope(array $operation): bool {
		if (sizeof($operation) !== 1) {
			return false;
		}
		$key = array_keys($operation)[0];
		return in_array($key, ['update', 'replace', 'delete', 'create', 'index'], true);
	}

	/**
	 * @param array $doc
	 * @param string $prefix
	 * @return void
	 */
	private function rejectNulls(array $doc, string $prefix = ''): void {
		foreach ($doc as $key => $value) {
			$path = $prefix === '' ? (string)$key : $prefix . '[' . $key . ']';
			if ($value === null) {
				throw new RuntimeException("Error: The key '{$path}' in document has a null value.\n");
			}
			if (!is_array($value)) {
				continue;
			}

			$this->rejectNulls($value, $path);
		}
	}
}
