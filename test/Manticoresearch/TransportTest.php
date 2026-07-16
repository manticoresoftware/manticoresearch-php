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

		$this->assertSame(
			[
				'Content-Type: application/json',
				'Authorization: Basic ' . base64_encode('admin:secret'),
			],
			$this->getRequestHeaders(new Http($basicConnection), $request, $basicConnection)
		);
		$this->assertSame(
			[
				'Content-Type: application/json',
				'Authorization: Bearer raw-token',
			],
			$this->getRequestHeaders(new Http($bearerConnection), $request, $bearerConnection)
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

		$this->assertSame(
			[
				'Content-Type' => 'application/json',
				'Authorization' => 'Basic ' . base64_encode('admin:secret'),
			],
			$this->getRequestHeaders(new PhpHttp($basicConnection), $request, $basicConnection)
		);
		$this->assertSame(
			[
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer raw-token',
			],
			$this->getRequestHeaders(new PhpHttp($bearerConnection), $request, $bearerConnection)
		);
	}

	private function getRequestHeaders($transport, Request $request, Connection $connection) {
		$class = new \ReflectionClass($transport);
		$method = $class->getMethod('getRequestHeaders');
		$method->setAccessible(true);
		return $method->invoke($transport, $request, $connection);
	}
}
