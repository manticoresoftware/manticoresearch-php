<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\Connection;
use Manticoresearch\Transport;
use Manticoresearch\Transport\Http;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Http\Discovery\Psr17FactoryDiscovery;

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
			'X-Forwarded-Host' => 'test.com'
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
}
