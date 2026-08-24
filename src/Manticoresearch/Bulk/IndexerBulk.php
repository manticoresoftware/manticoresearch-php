<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Bulk;

use Manticoresearch\Client;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Exceptions\RuntimeException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Orchestrates indexer-assisted bulk ingestion with optional adaptive concurrency.
 */
class IndexerBulk
{
	/** @var Client */
	private $client;

	/** @var LoggerInterface */
	private $logger;

	/** @var OperationNormalizer|null */
	private $normalizer;

	/**
	 * @param Client $client
	 * @param LoggerInterface|null $logger
	 * @param OperationNormalizer|null $normalizer
	 */
	public function __construct(
		Client $client,
		?LoggerInterface $logger = null,
		?OperationNormalizer $normalizer = null
	) {
		$this->client = $client;
		$this->logger = $logger ?? new NullLogger();
		$this->normalizer = $normalizer;
	}

	/**
	 * @param iterable $operations
	 * @param array $options
	 * @return array
	 */
	public function run(iterable $operations, array $options = []): array {
		$opts = Options::fromArray($options);
		if ($opts->fallback && !is_array($operations)) {
			$operations = iterator_to_array($operations, false);
		}
		$normalizer = $this->normalizer ?? new OperationNormalizer();
		$source = new OperationIterator($operations, $normalizer);

		try {
			$connection = $this->client->getConnectionPool()->getConnection();
			$uploader = new CurlUploader($connection, $opts->timeout);
			$uploader->assertSupportedTransport();
		} catch (RuntimeException $e) {
			if ($opts->fallback) {
				return $this->fallbackBulk($operations, $e->getMessage());
			}
			throw $e;
		}

		$warnings = [];
		$cap = new WorkerCap($this->client);
		$status = $cap->status();
		$maxWorkers = $cap->resolveMax($opts->maxWorkers);

		$uploaded = [
			'responses' => [],
			'docs' => 0,
			'bytes' => 0,
			'requests' => 0,
			'elapsed' => 0.0,
		];
		$tuning = [
			'stages' => [],
			'throughput_by_workers' => [],
		];

		if ($opts->workers !== null) {
			$selectedWorkers = max(1, min($opts->workers, $maxWorkers));
			if ($opts->workers > $maxWorkers) {
				$warnings[] = 'Requested workers=' . $opts->workers
					. ' capped to max_workers=' . $maxWorkers;
			}
		} else {
			try {
				$tuner = new AdaptiveTuner($opts->gainThreshold, $maxWorkers);
				$tuned = $this->autoTune($source, $uploader, $tuner, $opts, $warnings);
			} catch (ResponseException $e) {
				if ($opts->fallback && $this->isCapabilityError($e)) {
					return $this->fallbackBulk($operations, $e->getMessage());
				}
				throw $e;
			} catch (RuntimeException $e) {
				if ($opts->fallback && $this->isCapabilityErrorMessage($e->getMessage())) {
					return $this->fallbackBulk($operations, $e->getMessage());
				}
				throw $e;
			}
			$selectedWorkers = $tuned['selected_workers'];
			$tuning = [
				'stages' => $tuned['stages'],
				'throughput_by_workers' => $tuned['throughput_by_workers'],
			];
			$uploaded['responses'] = $tuned['probe_responses'];
			$uploaded['docs'] = $tuned['probe_docs'];
			$uploaded['bytes'] = $tuned['probe_bytes'];
			$uploaded['requests'] = $tuned['probe_requests'];
			$uploaded['elapsed'] = $tuned['probe_elapsed'];
		}

		try {
			$rest = $this->uploadAll($source, $uploader, $selectedWorkers, $opts);
		} catch (ResponseException $e) {
			if ($opts->fallback && $uploaded['docs'] === 0 && $this->isCapabilityError($e)) {
				return $this->fallbackBulk($operations, $e->getMessage());
			}
			throw $e;
		}

		$uploaded['responses'] = array_merge($uploaded['responses'], $rest['responses']);
		$uploaded['docs'] += $rest['docs'];
		$uploaded['bytes'] += $rest['bytes'];
		$uploaded['requests'] += $rest['requests'];
		$uploaded['elapsed'] += $rest['elapsed'];

		$throughput = ((float)$uploaded['elapsed']) > 0.0
			? $uploaded['docs'] / $uploaded['elapsed']
			: 0.0;

		return [
			'items' => $uploaded['responses'],
			'docs' => $uploaded['docs'],
			'bytes' => $uploaded['bytes'],
			'requests' => $uploaded['requests'],
			'elapsed' => $uploaded['elapsed'],
			'throughput' => $throughput,
			'selected_workers' => $selectedWorkers,
			'max_workers' => $maxWorkers,
			'workers_status' => $status,
			'tuning' => $tuning,
			'warnings' => $warnings,
			'mode' => 'indexer_rt_bulk',
		];
	}

	/**
	 * @param OperationIterator $source
	 * @param CurlUploader $uploader
	 * @param AdaptiveTuner $tuner
	 * @param Options $opts
	 * @param array $warnings
	 * @return array
	 */
	private function autoTune(
		OperationIterator $source,
		CurlUploader $uploader,
		AdaptiveTuner $tuner,
		Options $opts,
		array &$warnings
	): array {
		$probeResponses = [];
		$probeDocs = 0;
		$probeBytes = 0;
		$probeRequests = 0;
		$probeElapsed = 0.0;

		$probe = function (int $workers) use (
			$source,
			$uploader,
			$opts,
			&$probeResponses,
			&$probeDocs,
			&$probeBytes,
			&$probeRequests,
			&$probeElapsed,
			&$warnings
		) {
			$streams = $this->takeStreams($source, $workers, $opts);
			if ($streams === []) {
				return 0.0;
			}
			try {
				$result = $uploader->uploadStreams($streams, sizeof($streams));
			} catch (ResponseException $e) {
				if ($this->isCapabilityError($e)) {
					$warnings[] = $e->getMessage();
				}
				throw $e;
			}
			$probeResponses = array_merge($probeResponses, $result['responses']);
			$probeDocs += $result['docs'];
			$probeBytes += $result['bytes'];
			$probeRequests += $result['requests'];
			$probeElapsed += $result['elapsed'];
			return $result['elapsed'] > 0 ? ($result['docs'] / $result['elapsed']) : 0.0;
		};

		$tuning = $tuner->tune($probe);
		$tuning['probe_responses'] = $probeResponses;
		$tuning['probe_docs'] = $probeDocs;
		$tuning['probe_bytes'] = $probeBytes;
		$tuning['probe_requests'] = $probeRequests;
		$tuning['probe_elapsed'] = $probeElapsed;
		return $tuning;
	}

	/**
	 * Drain the remaining source using waves of concurrent streams.
	 *
	 * @param OperationIterator $source
	 * @param CurlUploader $uploader
	 * @param int $workers
	 * @param Options $opts
	 * @return array
	 */
	private function uploadAll(
		OperationIterator $source,
		CurlUploader $uploader,
		int $workers,
		Options $opts
	): array {
		$aggregate = [
			'responses' => [],
			'docs' => 0,
			'bytes' => 0,
			'requests' => 0,
			'elapsed' => 0.0,
		];
		while (true) {
			$streams = $this->takeStreams($source, $workers, $opts);
			if ($streams === []) {
				break;
			}
			$result = $uploader->uploadStreams($streams, sizeof($streams));
			$aggregate['responses'] = array_merge($aggregate['responses'], $result['responses']);
			$aggregate['docs'] += $result['docs'];
			$aggregate['bytes'] += $result['bytes'];
			$aggregate['requests'] += $result['requests'];
			$aggregate['elapsed'] += $result['elapsed'];
		}
		return $aggregate;
	}

	/**
	 * Open up to $count streams that share $source (pulling happens during upload).
	 *
	 * @param OperationIterator $source
	 * @param int $count
	 * @param Options $opts
	 * @return NdjsonStream[]
	 */
	private function takeStreams(OperationIterator $source, int $count, Options $opts): array {
		$streams = [];
		for ($i = 0; $i < $count; $i++) {
			$stream = NdjsonStream::tryCreate($source, $opts->batchSize, $opts->batchBytes);
			if ($stream === null) {
				break;
			}
			$streams[] = $stream;
		}
		return $streams;
	}

	/**
	 * @param iterable $operations
	 * @param string $reason
	 * @return array
	 */
	private function fallbackBulk(iterable $operations, string $reason): array {
		$this->logger->warning('Falling back to regular bulk: ' . $reason);
		$body = [];
		$normalizer = $this->normalizer ?? new OperationNormalizer(null, false);
		foreach ($operations as $operation) {
			try {
				$payload = $normalizer->normalize($operation);
			} catch (RuntimeException $e) {
				if (is_array($operation) && isset($operation['insert'])) {
					$body[] = $operation;
					continue;
				}
				throw $e;
			}
			$body[] = ['insert' => $payload];
		}
		$response = $this->client->bulk(['body' => $body]);
		return [
			'items' => isset($response['items']) ? $response['items'] : $response,
			'docs' => sizeof($body),
			'bytes' => null,
			'requests' => 1,
			'elapsed' => null,
			'throughput' => null,
			'selected_workers' => 1,
			'max_workers' => 1,
			'workers_status' => [],
			'tuning' => ['stages' => [], 'throughput_by_workers' => []],
			'warnings' => ['Fell back to regular /bulk: ' . $reason],
			'mode' => 'bulk_fallback',
		];
	}

	/**
	 * @param ResponseException $e
	 * @return bool
	 */
	private function isCapabilityError(ResponseException $e): bool {
		return $this->isCapabilityErrorMessage((string)$e->getMessage());
	}

	/**
	 * @param string $message
	 * @return bool
	 */
	private function isCapabilityErrorMessage(string $message): bool {
		$message = strtolower($message);
		$needles = [
			'indexer_rt_bulk',
			'indexer rt bulk',
			'unknown option',
			'unknown query',
			'unsupported',
			'unavailable',
			'failed to start indexer',
			'failed to locate',
			'sibling',
			'requires http/https',
		];
		foreach ($needles as $needle) {
			if (strpos($message, $needle) !== false) {
				return true;
			}
		}
		return false;
	}
}
