<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch;

use Manticoresearch\Response\Token;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Transport
 * @package Manticoresearch
 */
class Transport
{
	/**
	 * @var Connection
	 */
	protected $connection;

	/**
	 * @var LoggerInterface|NullLogger
	 */
	protected $logger;

	/**
	 * Transport constructor.
	 * @param Connection|null $connection
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(?Connection $connection = null, ?LoggerInterface $logger = null) {
		if ($connection) {
			$this->connection = $connection;
		}
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * @return Connection|null
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * @param Connection $connection
	 * @return Transport
	 */
	public function setConnection(Connection $connection): Transport {
		$this->connection = $connection;
		return $this;
	}

	/**
	 * @param string $transport
	 * @param Connection $connection
	 * @param LoggerInterface $logger
	 * @param array<mixed> $clientParams
	 * @return mixed
	 * @throws \Exception
	 */
	public static function create(
		string $transport,
		Connection $connection,
		LoggerInterface $logger,
		array $clientParams = []
	) {
		if (is_string($transport)) {
			$className = "Manticoresearch\\Transport\\$transport";
			if (class_exists($className)) {
				$transport = new $className($connection, $logger, ...$clientParams);
			}
		}
		if (!($transport instanceof self)) {
			throw new \Exception('Bad transport');
		}

		$transport->setConnection($connection);
		return $transport;
	}

	/**
	 * @param string $uri
	 * @param array $query
	 * @return string
	 */
	protected function setupURI(string $uri, $query = []): string {
		if (!empty($query)) {
			foreach ($query as $k => $v) {
				if (!is_bool($v)) {
					continue;
				}

				$query[$k] = $v ? 'true' : 'false';
			}
			$uri .= '?' . http_build_query($query);
		}
		return $uri;
	}

	/**
	 * Build Authorization header value for the wire from connection config.
	 *
	 * @param Connection $connection
	 * @return string|null
	 */
	protected function buildAuthorizationHeader(Connection $connection): ?string {
		$bearerToken = $connection->getConfig('bearer_token');
		if ($bearerToken !== null) {
			return 'Bearer ' . $bearerToken;
		}
		$username = $connection->getConfig('username');
		$password = $connection->getConfig('password');
		if ($username !== null && $password !== null) {
			return 'Basic ' . base64_encode($username . ':' . $password);
		}
		return null;
	}

	/**
	 * Request headers as a list of "Header: value" strings (curl).
	 *
	 * @param Request $request
	 * @param Connection $connection
	 * @return array
	 */
	protected function getRequestHeadersAsList(Request $request, Connection $connection): array {
		$headers = $connection->getHeaders();
		$headers[] = sprintf('Content-Type: %s', $request->getContentType());
		$authorization = $this->buildAuthorizationHeader($connection);
		if ($authorization === null) {
			return $headers;
		}

		$headers = array_values(
			array_filter(
				$headers,
				static function ($header) {
					return stripos((string)$header, 'Authorization:') !== 0;
				}
			)
		);
		$headers[] = 'Authorization: ' . $authorization;
		return $headers;
	}

	/**
	 * Request headers as an associative map (PSR-7 / PhpHttp).
	 *
	 * @param Request $request
	 * @param Connection $connection
	 * @return array
	 */
	protected function getRequestHeadersAsMap(Request $request, Connection $connection): array {
		$headers = $connection->getHeaders();
		$headers['Content-Type'] = $request->getContentType();
		$authorization = $this->buildAuthorizationHeader($connection);
		if ($authorization === null) {
			return $headers;
		}

		foreach (array_keys($headers) as $header) {
			if (strcasecmp((string)$header, 'Authorization') !== 0) {
				continue;
			}
			unset($headers[$header]);
		}
		$headers['Authorization'] = $authorization;
		return $headers;
	}

	/**
	 * @param mixed $body
	 * @param Request $request
	 * @param Response $response
	 * @return mixed
	 */
	protected function getResponseBodyForLogging($body, Request $request, Response $response) {
		if ($request->getPath() === '/token' || $response instanceof Token) {
			return '[redacted]';
		}
		return $body;
	}
}
