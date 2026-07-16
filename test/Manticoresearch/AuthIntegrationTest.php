<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\Client;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Exceptions\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * Live authentication checks against a dedicated Manticore instance.
 *
 * Requires MS_AUTH_HOST, MS_AUTH_PORT, MS_AUTH_USERNAME, and MS_AUTH_PASSWORD.
 * Run with: vendor/bin/phpunit --testsuite auth
 */
class AuthIntegrationTest extends TestCase
{
	/** @var string */
	private $host;

	/** @var string|int */
	private $port;

	/** @var string */
	private $username;

	/** @var string */
	private $password;

	/** @var string */
	private $transport;

	protected function setUp(): void {
		$host = $_SERVER['MS_AUTH_HOST'] ?? getenv('MS_AUTH_HOST') ?: null;
		$port = $_SERVER['MS_AUTH_PORT'] ?? getenv('MS_AUTH_PORT') ?: null;
		$username = $_SERVER['MS_AUTH_USERNAME'] ?? getenv('MS_AUTH_USERNAME') ?: null;
		$password = $_SERVER['MS_AUTH_PASSWORD'] ?? getenv('MS_AUTH_PASSWORD') ?: null;

		if ($host === null || $port === null || $username === null || $password === null) {
			$this->markTestSkipped(
				'Auth integration tests require MS_AUTH_HOST, MS_AUTH_PORT, '
				. 'MS_AUTH_USERNAME, and MS_AUTH_PASSWORD'
			);
		}

		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->transport = empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'];
	}

	public function testUnauthenticatedRequestIsRejected() {
		$client = new Client($this->baseConnectionParams());

		try {
			$client->nodes()->status();
			$this->fail('Unauthenticated request was accepted');
		} catch (ResponseException | RuntimeException $e) {
			$this->assertNotSame('', $e->getMessage());
		}
	}

	public function testBasicAuthenticationSucceeds() {
		$client = $this->createBasicClient();

		$response = $client->nodes()->status();

		$this->assertIsArray($response);
		$this->assertArrayHasKey('uptime', $response);
	}

	public function testTokenEndpointReturnsBearerToken() {
		$client = $this->createBasicClient();

		$token = $client->token();

		$this->assertIsString($token);
		$this->assertNotSame('', $token);
	}

	public function testBearerTokenAuthenticationSucceeds() {
		$token = $this->createBasicClient()->token();
		$client = new Client(
			$this->baseConnectionParams() + [
				'bearer_token' => $token,
			]
		);

		$response = $client->nodes()->status();

		$this->assertIsArray($response);
		$this->assertArrayHasKey('uptime', $response);
	}

	public function testInvalidBasicCredentialsAreRejected() {
		$client = new Client(
			$this->baseConnectionParams() + [
				'username' => $this->username,
				'password' => $this->password . '-invalid',
			]
		);

		try {
			$client->nodes()->status();
			$this->fail('Request with invalid Basic credentials was accepted');
		} catch (ResponseException | RuntimeException $e) {
			$this->assertNotSame('', $e->getMessage());
		}
	}

	public function testInvalidBearerTokenIsRejected() {
		$client = new Client(
			$this->baseConnectionParams() + [
				'bearer_token' => 'invalid-bearer-token',
			]
		);

		try {
			$client->nodes()->status();
			$this->fail('Request with invalid bearer token was accepted');
		} catch (ResponseException | RuntimeException $e) {
			$this->assertNotSame('', $e->getMessage());
		}
	}

	private function createBasicClient(): Client {
		return new Client(
			$this->baseConnectionParams() + [
				'username' => $this->username,
				'password' => $this->password,
			]
		);
	}

	private function baseConnectionParams(): array {
		return [
			'host' => $this->host,
			'port' => $this->port,
			'transport' => $this->transport,
		];
	}
}
