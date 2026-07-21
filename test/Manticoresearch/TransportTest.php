<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Http\Discovery\Psr17FactoryDiscovery;
use Manticoresearch\Connection;
use Manticoresearch\Request;
use Manticoresearch\Transport;
use Manticoresearch\Transport\Http;
use Manticoresearch\Transport\PhpHttp;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class TransportTest extends TestCase
{
	public function testBadStaticTransportCreate() {
		$connection = new Connection([]);
		$this->expectException('Exception');
		$this->expectExceptionMessage('Bad transport');
		Transport::create('badtransport', $connection, new NullLogger());
	}

	public function testStaticTransportCreateWithClientParams() {
		$connection = new Connection([]);
		$httpTransport = Transport::create('Http', $connection, new NullLogger(), ['xyz']);
		$this->assertInstanceOf(Http::class, $httpTransport);
	}

	public function testSetUpURI() {
		$transport = new Transport();
		$class = new \ReflectionClass(Transport::class);
		$method = $class->getMethod('setupURI');
		$method->setAccessible(true);

		$url = $method->invokeArgs($transport, ['/search', ['a' => 1, 'b' => false]]);
		$this->assertEquals('/search?a=1&b=false', $url);
	}

	public function testTransportMessageFactory() {
		$method = 'GET';
		$uriPath = '/test';
		$headers = [
			'X-Forwarded-Host' => ['test.com'],
		];
		$content = 'test content';

		$messageFactory = Psr17FactoryDiscovery::findRequestFactory();
		$message = $messageFactory->createRequest($method, $uriPath, $headers, $content);
		foreach ($headers as $key => $value) {
			$message = $message->withAddedHeader($key, $value);
		}
		if (!empty($content) && empty($message->getBody()->getContents())) {
			$message = $message->withBody($messageFactory->createStream($content));
		}

		$this->assertEquals($method, $message->getMethod());
		$this->assertEquals($uriPath, $message->getUri()->getPath());
		$this->assertEquals($headers, $message->getHeaders());
		$this->assertEquals($content, $message->getBody()->getContents());
	}

	public function testHttpTransportAuthenticationHeaders() {
		$request = new Request(['body' => []]);
		$basicConnection = new Connection(
			[
			'persistent' => false,
			'username' => 'admin',
			'password' => 'secret',
			'headers' => ['Authorization: Custom'],
			]
		);
		$bearerConnection = new Connection(
			[
			'persistent' => false,
			'bearer_token' => 'raw-token',
			]
		);

		$basicHeaders = $this->invokeProtected(
			new Http($basicConnection),
			'getRequestHeadersAsList',
			[$request, $basicConnection]
		);
		$bearerHeaders = $this->invokeProtected(
			new Http($bearerConnection),
			'getRequestHeadersAsList',
			[$request, $bearerConnection]
		);

		$this->assertSame(
			[
				'Content-Type: application/json',
				'Authorization: Basic ' . base64_encode('admin:secret'),
			],
			$basicHeaders
		);
		$this->assertSame(
			[
				'Content-Type: application/json',
				'Authorization: Bearer raw-token',
			],
			$bearerHeaders
		);
		$this->assertSame(
			[
				'Content-Type: application/json',
				'Authorization: Basic ***',
			],
			Connection::redactAuthHeaders($basicHeaders)
		);
		$this->assertSame(
			[
				'Content-Type: application/json',
				'Authorization: Bearer ***',
			],
			Connection::redactAuthHeaders($bearerHeaders)
		);
	}

	public function testPhpHttpTransportAuthenticationHeaders() {
		$request = new Request(['body' => []]);
		$basicConnection = new Connection(
			[
			'persistent' => false,
			'username' => 'admin',
			'password' => 'secret',
			'headers' => ['authorization' => 'Custom'],
			]
		);
		$bearerConnection = new Connection(
			[
			'persistent' => false,
			'bearer_token' => 'raw-token',
			]
		);

		$basicHeaders = $this->invokeProtected(
			new PhpHttp($basicConnection),
			'getRequestHeadersAsMap',
			[$request, $basicConnection]
		);
		$bearerHeaders = $this->invokeProtected(
			new PhpHttp($bearerConnection),
			'getRequestHeadersAsMap',
			[$request, $bearerConnection]
		);

		$this->assertSame(
			[
				'Content-Type' => 'application/json',
				'Authorization' => 'Basic ' . base64_encode('admin:secret'),
			],
			$basicHeaders
		);
		$this->assertSame(
			[
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer raw-token',
			],
			$bearerHeaders
		);
		$this->assertSame(
			[
				'Content-Type' => 'application/json',
				'Authorization' => 'Basic ***',
			],
			Connection::redactAuthHeaders($basicHeaders)
		);
		$this->assertSame(
			[
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ***',
			],
			Connection::redactAuthHeaders($bearerHeaders)
		);
	}

	public function testIncompleteBasicCredentialsProduceNoAuthorizationHeader() {
		$request = new Request(['body' => []]);
		$connection = new Connection(
			[
			'persistent' => false,
			'username' => 'admin',
			]
		);

		$headers = $this->invokeProtected(
			new Http($connection),
			'getRequestHeadersAsList',
			[$request, $connection]
		);

		$this->assertSame(['Content-Type: application/json'], $headers);
		$this->assertNull(
			$this->invokeProtected(new Http($connection), 'buildAuthorizationHeader', [$connection])
		);
	}

	public function testTokenResponseBodyIsRedactedForLogging() {
		$transport = new Http(new Connection(['persistent' => false]));
		$request = new Request(['body' => []]);
		$request->setPath('/token');
		$tokenResponse = new \Manticoresearch\Response\Token('"issued-token"', 200);

		$redacted = $this->invokeProtected(
			$transport,
			'getResponseBodyForLogging',
			['issued-token', $request, $tokenResponse]
		);
		$this->assertSame('[redacted]', $redacted);

		$searchRequest = new Request(['body' => []]);
		$searchRequest->setPath('/search');
		$searchResponse = new \Manticoresearch\Response('{"hits":[]}', 200);
		$kept = $this->invokeProtected(
			$transport,
			'getResponseBodyForLogging',
			[['hits' => []], $searchRequest, $searchResponse]
		);
		$this->assertSame(['hits' => []], $kept);
	}

	private function invokeProtected($object, string $method, array $args = []) {
		$class = new \ReflectionClass($object);
		$refMethod = $class->getMethod($method);
		$refMethod->setAccessible(true);
		return $refMethod->invokeArgs($object, $args);
	}
}
