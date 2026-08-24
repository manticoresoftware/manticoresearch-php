<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Bulk;

/**
 * Chooses concurrent request count by exponential probing of throughput.
 */
class AdaptiveTuner
{
	/** @var float */
	private $gainThreshold;

	/** @var int */
	private $maxWorkers;

	/**
	 * @param float $gainThreshold
	 * @param int $maxWorkers
	 */
	public function __construct(float $gainThreshold, int $maxWorkers) {
		$this->gainThreshold = $gainThreshold;
		$this->maxWorkers = max(1, $maxWorkers);
	}

	/**
	 * @return int
	 */
	public function getMaxWorkers(): int {
		return $this->maxWorkers;
	}

	/**
	 * Yield candidate worker counts: 1, 2, 4, ... up to max.
	 *
	 * @return int[]
	 */
	public function candidates(): array {
		$values = [];
		for ($n = 1; $n <= $this->maxWorkers; $n *= 2) {
			$values[] = $n;
			if ($n > intdiv($this->maxWorkers, 2)) {
				break;
			}
		}
		if ($values[sizeof($values) - 1] !== $this->maxWorkers) {
			$values[] = $this->maxWorkers;
		}
		return array_values(array_unique($values));
	}

	/**
	 * Decide whether a newer probe is better enough to keep searching.
	 *
	 * @param float $previousThroughput docs/sec (or bytes/sec)
	 * @param float $currentThroughput
	 * @return bool true when current is an improvement worth continuing from
	 */
	public function isImprovement(float $previousThroughput, float $currentThroughput): bool {
		if ($previousThroughput <= 0) {
			return $currentThroughput > 0;
		}
		return ($currentThroughput - $previousThroughput) / $previousThroughput >= $this->gainThreshold;
	}

	/**
	 * Pick the worker count with the best throughput; ties prefer fewer workers.
	 *
	 * @param array<int,float> $throughputByWorkers worker => throughput
	 * @return int
	 */
	public function selectBest(array $throughputByWorkers): int {
		if ($throughputByWorkers === []) {
			return 1;
		}
		$bestWorkers = 1;
		$bestRate = -1.0;
		ksort($throughputByWorkers);
		foreach ($throughputByWorkers as $workers => $rate) {
			if ($rate <= $bestRate) {
				continue;
			}

			$bestRate = $rate;
			$bestWorkers = (int)$workers;
		}
		return max(1, min($bestWorkers, $this->maxWorkers));
	}

	/**
	 * Walk candidates using a callable probe: fn(int $workers): float throughput.
	 * Stops early when improvement falls below the gain threshold.
	 *
	 * @param callable $probe
	 * @return array{selected_workers:int,stages:array,throughput_by_workers:array}
	 */
	public function tune(callable $probe): array {
		$stages = [];
		$rates = [];
		$previous = null;

		foreach ($this->candidates() as $workers) {
			$rate = (float)$probe($workers);
			$rates[$workers] = $rate;
			$stages[] = [
				'workers' => $workers,
				'throughput' => $rate,
			];
			if ($previous === null) {
				$previous = $rate;
				continue;
			}
			if ($this->isImprovement($previous, $rate)) {
				$previous = $rate;
				continue;
			}
			// No meaningful gain — stop probing higher concurrency
			break;
		}

		return [
			'selected_workers' => $this->selectBest($rates),
			'stages' => $stages,
			'throughput_by_workers' => $rates,
		];
	}
}
