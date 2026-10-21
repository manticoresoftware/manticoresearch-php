<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Bulk;

use Manticoresearch\Client;

/**
 * Resolves a concurrency ceiling from Manticore SHOW STATUS workers_total.
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
	 * @return int|null
	 */
	public function workersTotal() {
		try {
			$rows = $this->client->nodes()->status(['body' => ['pattern' => 'workers_total']]);
		} catch (\Throwable $e) {
			return null;
		}
		if (!is_array($rows)) {
			return null;
		}
		if (array_key_exists('workers_total', $rows)) {
			return self::toInt($rows['workers_total']);
		}
		foreach ($rows as $row) {
			if (!is_array($row)) {
				continue;
			}
			$name = $row['Variable_name'] ?? $row['Counter'] ?? $row['Key'] ?? null;
			if ((string)$name !== 'workers_total') {
				continue;
			}
			return self::toInt($row['Value'] ?? null);
		}
		return null;
	}

	/**
	 * Default hard concurrency ceiling. Falls back to 1 when status is unavailable.
	 *
	 * @param int|null $configuredMax
	 * @return int
	 */
	public function resolveMax(?int $configuredMax = null): int {
		$serverMax = $this->workersTotal();
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
