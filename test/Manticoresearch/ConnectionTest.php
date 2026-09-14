<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\Connection;
use Manticoresearch\Exceptions\RuntimeException;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
	/** @var Connection */
	private $connection;

	public function setUp():void {
		parent::setUp();
		$this->connection = new Connection([]);
	}

	public function testSetHostGetHost() {
		$this->connection->setHost('example.com');
		$this->assertEquals('example.com', $this->connection->getHost());
	}

	public function testSetPathGetPath() {
		$this->connection->setPath('/example');
		$this->assertEquals('/example', $this->connection->getPath());
	}

	public function testSetPortGetPort() {
		$this->connection->setPort(19308);
		$this->assertEquals(19308, $this->connection->getPort());
	}

	public function testSetTimeoutGetTimeout() {
		$this->connection->setTimeout(12);
		$this->assertEquals(12, $this->connection->getTimeout());
	}

	public function testSetConnectTimeoutGetConnectTimeout() {
		$this->connection->setConnectTimeout(5);
		$this->assertEquals(5, $this->connection->getConnectTimeout());
	}

	public function testSetTransportGetTransport() {
		$this->connection->setTransport('http');
		$this->assertEquals('http', $this->connection->getTransport());
	}

	public function testSetHeadersGetHeaders() {
		$headers = [
			'a' => 1,
			'b' => 2,
		];

		$this->connection->setheaders($headers);
		$this->assertEquals($headers, $this->connection->getHeaders());
	}

	public function testSetConfigGetAllConfig() {
		$config = [
			'a' => 1,
			'b' => 2,
		];

		$this->connection->setConfig($config);

		$configReturned = $this->connection->getConfig();
		$keys = array_keys($configReturned);
		sort($keys);

		$this->assertEquals(
			[
			'a',
			'b',
			'connect_timeout',
			'curl',
			'headers',
			'host',
			'password',
			'path',
			'persistent',
			'port',
			'proxy',
			'scheme',
			'timeout',
			'transport',
			'username',

			], $keys
		);
	}

	public function testSetConfigGetConfigByKey() {
		$config = [
			'a' => 1,
			'b' => 2,
		];

		$this->connection->setConfig($config);
		$this->assertEquals(2, $this->connection->getConfig('b'));
	}

	public function testBasicAuthorizationHeader() {
		$connection = new Connection(
			[
			'persistent' => false,
			'username' => 'admin',
			'password' => 'secret',
			]
		);

		$this->assertSame('secret', $connection->getConfig('password'));
		$loggingConfig = $connection->getConfigForLogging();
		$this->assertSame('***', $loggingConfig['password']);
		$this->assertSame('admin', $loggingConfig['username']);
	}

	public function testBearerTokenRedactedInLoggingConfig() {
		$connection = new Connection(
			[
			'persistent' => false,
			'bearer_token' => 'raw-token',
			]
		);

		$this->assertSame('raw-token', $connection->getConfig('bearer_token'));
		$this->assertSame('***', $connection->getConfigForLogging()['bearer_token']);
	}

	public function testIncompleteBasicCredentials() {
		$connection = new Connection(
			[
			'persistent' => false,
			'username' => 'admin',
			]
		);

		$this->assertNull($connection->getConfig('password'));
		$this->assertArrayNotHasKey('bearer_token', $connection->getConfigForLogging());
	}

	public function testBasicAndBearerAuthenticationCombineFail() {
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Basic and bearer authentication cannot be configured together');

		new Connection(
			[
			'persistent' => false,
			'username' => 'admin',
			'password' => 'secret',
			'bearer_token' => 'raw-token',
			]
		);
	}

	public function testSetConfigRejectsConflictingAuthentication() {
		$connection = new Connection(
			[
			'persistent' => false,
			'username' => 'admin',
			'password' => 'secret',
			]
		);

		try {
			$connection->setConfig(['bearer_token' => 'raw-token']);
			$this->fail('Conflicting authentication was accepted');
		} catch (RuntimeException $exception) {
			$this->assertSame(
				'Basic and bearer authentication cannot be configured together',
				$exception->getMessage()
			);
		}

		$this->assertNull($connection->getConfig('bearer_token'));
		$this->assertSame('secret', $connection->getConfig('password'));
		$this->assertSame('***', $connection->getConfigForLogging()['password']);
	}

	public function testRedactAuthorizationHeadersListAndMap() {
		$list = Connection::redactAuthHeaders(
			[
				'Content-Type: application/json',
				'Authorization: Basic ' . base64_encode('admin:secret'),
			]
		);
		$this->assertSame(
			[
				'Content-Type: application/json',
				'Authorization: Basic ***',
			],
			$list
		);

		$map = Connection::redactAuthHeaders(
			[
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer raw-token',
			]
		);
		$this->assertSame(
			[
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ***',
			],
			$map
		);

		$custom = Connection::redactAuthHeaders(
			[
				'authorization' => 'CustomSecret',
			]
		);
		$this->assertSame(['authorization' => '***'], $custom);
	}

	public function testRedactSensitiveConfigProxyAndHeaders() {
		$redacted = Connection::redactConfig(
			[
				'password' => 'secret',
				'bearer_token' => 'tok',
				'username' => 'admin',
				'proxy' => 'user:pass@proxy.example:8080',
				'headers' => ['Authorization: Bearer tok'],
			]
		);

		$this->assertSame('***', $redacted['password']);
		$this->assertSame('***', $redacted['bearer_token']);
		$this->assertSame('admin', $redacted['username']);
		$this->assertSame('***:***@proxy.example:8080', $redacted['proxy']);
		$this->assertSame(['Authorization: Bearer ***'], $redacted['headers']);
	}

	public function testStaticCreateSelf() {
		$newConnection = Connection::create($this->connection);
		$this->assertEquals($this->connection, $newConnection);
	}

	public function testStaticCreateEmptyParams() {
		$newConnection = Connection::create([]);
		// Fix for php 7.4 since it treats Connection objects with different curl instances as not equal
		$this->assertEquals($this->connection->getConfig(), $newConnection->getConfig());
	}

	public function testStaticCreateInvalidParams() {
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('connection must receive array of parameters or self');
		$newConnection = Connection::create('this is invalid');
		$this->assertEquals($this->connection, $newConnection);
	}
}
