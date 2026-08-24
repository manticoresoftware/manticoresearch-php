<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Bulk;

use Manticoresearch\Exceptions\RuntimeException;

/**
 * Parsed options for indexer-assisted bulk ingestion.
 */
class Options
{
	/** @var int|null */
	public $workers;

	/** @var int|null */
	public $maxWorkers;

	/** @var int */
	public $batchSize;

	/** @var int */
	public $batchBytes;

	/** @var float Minimum relative throughput gain to keep doubling workers */
	public $gainThreshold;

	/** @var int|null */
	public $timeout;

	/** @var bool Fall back to regular Client::bulk() when assisted mode is unavailable */
	public $fallback;

	/** @var int Documents per probe stage when auto-tuning */
	public $probeDocs;

	/**
	 * @param array $options
	 * @return self
	 */
	public static function fromArray(array $options = []): self {
		$parsed = new self();
		$parsed->workers = self::optionalPositiveInt($options, 'workers');
		$parsed->maxWorkers = self::optionalPositiveInt($options, 'max_workers');
		$parsed->batchSize = self::positiveInt($options, 'batch_size', 1000);
		$parsed->batchBytes = self::positiveInt($options, 'batch_bytes', 8 * 1024 * 1024);
		$parsed->gainThreshold = isset($options['gain_threshold'])
			? (float)$options['gain_threshold']
			: 0.05;
		if ($parsed->gainThreshold < 0) {
			throw new RuntimeException('gain_threshold must be >= 0');
		}
		$parsed->timeout = self::optionalPositiveInt($options, 'timeout');
		$parsed->fallback = !empty($options['fallback']);
		$parsed->probeDocs = self::positiveInt($options, 'probe_docs', $parsed->batchSize);
		return $parsed;
	}

	/**
	 * @param array $options
	 * @param string $key
	 * @return int|null
	 */
	private static function optionalPositiveInt(array $options, string $key) {
		if (!array_key_exists($key, $options) || $options[$key] === null) {
			return null;
		}
		$value = (int)$options[$key];
		if ($value < 1) {
			throw new RuntimeException($key . ' must be >= 1');
		}
		return $value;
	}

	/**
	 * @param array $options
	 * @param string $key
	 * @param int $default
	 * @return int
	 */
	private static function positiveInt(array $options, string $key, int $default): int {
		if (!array_key_exists($key, $options) || $options[$key] === null) {
			return $default;
		}
		$value = (int)$options[$key];
		if ($value < 1) {
			throw new RuntimeException($key . ' must be >= 1');
		}
		return $value;
	}
}
