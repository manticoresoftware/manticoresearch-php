<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Bulk;

use Manticoresearch\Client;

/**
 * Resolves a concurrency ceiling from Manticore SHOW STATUS workers_* counters.
 */
class WorkerCap
{
	/** @var Client */
	private $client;

	/**
	 * @param Client $client
	 */
	public function __construct(Client $client) {
		$this->client = $client;
	}

	/**
	 * @return array{workers_total:?int,workers_active:?int,work_queue_length:?int}
	 */
	public function status(): array {
		$result = [
			'workers_total' => null,
			'workers_active' => null,
			'work_queue_length' => null,
		];
		try {
			$rows = $this->client->nodes()->status(['body' => ['pattern' => 'workers_%']]);
		} catch (\Throwable $e) {
			return $result;
		}
		if (!is_array($rows)) {
			return $result;
		}

		foreach (array_keys($result) as $key) {
			if (!array_key_exists($key, $rows)) {
				continue;
			}
			$result[$key] = self::toInt($rows[$key]);
		}

		// Also accept raw row lists if customMapping was not applied.
		foreach ($rows as $row) {
			if (!is_array($row)) {
				continue;
			}
			$name = $row['Variable_name'] ?? $row['Counter'] ?? $row['Key'] ?? null;
			if ($name === null || !array_key_exists((string)$name, $result)) {
				continue;
			}
			$value = $row['Value'] ?? null;
			if ($value === null) {
				continue;
			}

			$result[(string)$name] = self::toInt($value);
		}

		return $result;
	}

	/**
	 * Default hard concurrency ceiling. Falls back to 1 when status is unavailable.
	 *
	 * @param int|null $configuredMax
	 * @return int
	 */
	public function resolveMax(?int $configuredMax = null): int {
		$status = $this->status();
		$serverMax = $status['workers_total'];
		if ($serverMax === null || $serverMax < 1) {
			$serverMax = 1;
		}
		if ($configuredMax === null) {
			return $serverMax;
		}
		return max(1, min($configuredMax, $serverMax));
	}

	/**
	 * @param mixed $value
	 * @return int|null
	 */
	private static function toInt($value) {
		if (is_array($value)) {
			if (isset($value['Value'])) {
				$value = $value['Value'];
			} else {
				$value = reset($value);
			}
		}
		if ($value === null || $value === false || $value === '') {
			return null;
		}
		return (int)$value;
	}
}
