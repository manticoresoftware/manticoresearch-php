<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Bulk;

use Manticoresearch\Connection;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Request;
use Manticoresearch\Response\Bulk as BulkResponse;
use Manticoresearch\Transport;

/**
 * Concurrent chunked HTTP uploads to /bulk?indexer_rt_bulk=1 via curl_multi.
 *
 * Each request body is produced incrementally by NdjsonStream::read() — the full
 * NDJSON payload is never concatenated in memory.
 */
class CurlUploader
{
	/** @var Connection */
	private $connection;

	/** @var int|null */
	private $timeout;

	/**
	 * @param Connection $connection
	 * @param int|null $timeout
	 */
	public function __construct(Connection $connection, ?int $timeout = null) {
		$this->connection = $connection;
		$this->timeout = $timeout;
	}

	/**
	 * @return void
	 */
	public function assertSupportedTransport(): void {
		$transport = $this->connection->getTransport();
		// phpcs:ignore SlevomatCodingStandard.Classes.ModernClassNameReference.ClassNameReferencedViaFunctionCall
		$name = is_object($transport) ? get_class($transport) : (string)$transport;
		$base = $name;
		if (strpos($name, '\\') !== false) {
			$parts = explode('\\', $name);
			$base = end($parts);
		}
		if (!in_array($base, ['Http', 'Https'], true)) {
			throw new RuntimeException(
				'indexerBulk requires Http/Https (curl) transport; got ' . $base
			);
		}
	}

	/**
	 * Upload NDJSON streams concurrently (up to $workers in flight).
	 *
	 * When $phases is provided, accumulates:
	 * - http: curl_multi wait time excluding read-callback work
	 * - decode: response parse time in finalizeHandle
	 *
	 * @param NdjsonStream[] $streams
	 * @param int $workers
	 * @param array<string,float>|null $phases
	 * @return array{responses:array,docs:int,bytes:int,requests:int,elapsed:float}
	 */
	public function uploadStreams(array $streams, int $workers, &$phases = null): array {
		$this->assertSupportedTransport();
		$workers = max(1, $workers);
		$queue = [];
		foreach ($streams as $stream) {
			if (!($stream instanceof NdjsonStream) || $stream->isEmpty()) {
				continue;
			}

			$queue[] = $stream;
		}
		if ($queue === []) {
			return [
				'responses' => [],
				'docs' => 0,
				'bytes' => 0,
				'requests' => 0,
				'elapsed' => 0.0,
			];
		}

		$start = microtime(true);
		$multi = curl_multi_init();
		if ($multi === false) {
			throw new RuntimeException('Failed to initialize curl_multi');
		}

		$active = [];
		$responses = [];
		$totalDocs = 0;
		$totalBytes = 0;
		$requestCount = 0;
		$nextId = 0;
		$trackPhases = is_array($phases);
		$readCallbackTime = 0.0;

		try {
			while ($queue !== [] || $active !== []) {
				while (sizeof($active) < $workers && $queue !== []) {
					$stream = array_shift($queue);
					$handleId = $nextId++;
					$state = $this->createHandle($stream, $handleId, $readCallbackTime);
					$active[$handleId] = $state;
					curl_multi_add_handle($multi, $state['ch']);
				}

				$readBefore = $readCallbackTime;
				$httpStart = microtime(true);
				do {
					$status = curl_multi_exec($multi, $running);
				} while ($status === CURLM_CALL_MULTI_PERFORM);

				if ($status !== CURLM_OK) {
					throw new RuntimeException('curl_multi_exec failed with status ' . $status);
				}
				if ($trackPhases) {
					$phases['http'] += microtime(true) - $httpStart - ($readCallbackTime - $readBefore);
				}

				while ($info = curl_multi_info_read($multi)) {
					$ch = $info['handle'];
					$handleId = $this->findHandleId($active, $ch);
					if ($handleId === null) {
						curl_multi_remove_handle($multi, $ch);
						curl_close($ch);
						continue;
					}
					$state = $active[$handleId];
					$decodeStart = $trackPhases ? microtime(true) : 0.0;
					$response = $this->finalizeHandle($state, $info['result']);
					if ($trackPhases) {
						$phases['decode'] += microtime(true) - $decodeStart;
					}
					$responses[] = $response;
					$totalDocs += $response['docs'];
					$totalBytes += $response['bytes'];
					$requestCount++;
					curl_multi_remove_handle($multi, $ch);
					curl_close($ch);
					unset($active[$handleId]);
				}

				if (!$running || $active === []) {
					continue;
				}

				$httpStart = microtime(true);
				curl_multi_select($multi, 1.0);
				if ($trackPhases) {
					$phases['http'] += microtime(true) - $httpStart;
				}
			}
		} finally {
			foreach ($active as $state) {
				curl_multi_remove_handle($multi, $state['ch']);
				curl_close($state['ch']);
			}
			curl_multi_close($multi);
		}

		return [
			'responses' => $responses,
			'docs' => $totalDocs,
			'bytes' => $totalBytes,
			'requests' => $requestCount,
			'elapsed' => microtime(true) - $start,
		];
	}

	/**
	 * @param NdjsonStream $stream
	 * @param int $handleId
	 * @param float $readCallbackTime
	 * @return array
	 */
	private function createHandle(NdjsonStream $stream, int $handleId, float &$readCallbackTime): array {
		$connection = $this->connection;
		$scheme = $connection->getConfig('scheme') ?: 'http';
		$transport = $connection->getTransport();
		if ($transport === 'Https' || $scheme === 'https') {
			$scheme = 'https';
		}
		$url = $scheme . '://' . $connection->getHost() . ':' . $connection->getPort()
			. $connection->getPath() . '/bulk?indexer_rt_bulk=1';

		$ch = curl_init();
		$request = new Request(
			[
				'body' => '[indexer_rt_bulk stream]',
				'query' => ['indexer_rt_bulk' => 1],
				'content_type' => 'application/x-ndjson',
			]
		);
		$headers = (new Transport($connection))->getRequestHeadersAsList($request, $connection);
		$headers = array_values(
			array_filter(
				$headers,
				static function ($header) {
					return stripos((string)$header, 'Content-Length:') !== 0
						&& stripos((string)$header, 'Transfer-Encoding:') !== 0;
				}
			)
		);
		$headers[] = 'Transfer-Encoding: chunked';
		$headers[] = 'Expect:';

		$timeout = $this->timeout ?? $connection->getTimeout();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_UPLOAD, true);
		// Omit CURLOPT_INFILESIZE so libcurl sends Transfer-Encoding: chunked.
		curl_setopt(
			$ch,
			CURLOPT_READFUNCTION,
			// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter
			static function ($ch, $fd, $size) use ($stream, &$readCallbackTime) {
				$t = microtime(true);
				$data = $stream->read($size);
				$readCallbackTime += microtime(true) - $t;
				return $data;
			}
		);

		if ($connection->getConnectTimeout() > 0) {
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connection->getConnectTimeout());
		}
		if ($connection->getConfig('proxy') !== null) {
			curl_setopt($ch, CURLOPT_PROXY, $connection->getConfig('proxy'));
		}
		if (!empty($connection->getConfig('curl'))) {
			foreach ($connection->getConfig('curl') as $k => $v) {
				curl_setopt($ch, $k, $v);
			}
		}

		return [
			'id' => $handleId,
			'ch' => $ch,
			'url' => $url,
			'headers' => $headers,
			'stream' => $stream,
			'request' => $request,
		];
	}

	/**
	 * @param array $active
	 * @param resource|\CurlHandle $ch
	 * @return int|null
	 */
	private function findHandleId(array $active, $ch) {
		foreach ($active as $id => $state) {
			if ($state['ch'] === $ch) {
				return $id;
			}
		}
		return null;
	}

	/**
	 * @param array $state
	 * @param int $curlResult
	 * @return array
	 */
	private function finalizeHandle(array $state, int $curlResult): array {
		$ch = $state['ch'];
		/** @var NdjsonStream $stream */
		$stream = $state['stream'];
		$responseString = curl_multi_getcontent($ch);
		$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$errorno = curl_errno($ch);
		if ($curlResult !== CURLE_OK || $errorno > 0) {
			$error = curl_error($ch) ?: ('curl error ' . ($curlResult ?: $errorno));
			// Ambiguous after bytes may have left the client: do not auto-retry.
			throw new ConnectionException($error, $state['request']);
		}

		$response = new BulkResponse($responseString, $status);
		$response->setTransportInfo(
			[
				'url' => $state['url'],
				'headers' => Connection::redactAuthHeaders($state['headers']),
				'body' => '[indexer_rt_bulk ' . $stream->getDocs() . ' docs streamed]',
			]
		);
		if ($response->hasError()) {
			throw new ResponseException($state['request'], $response);
		}

		return [
			'status' => $status,
			'body' => $response->getResponse(),
			'docs' => $stream->getDocs(),
			'bytes' => $stream->getBytes(),
			'table' => $stream->getTable(),
		];
	}
}
