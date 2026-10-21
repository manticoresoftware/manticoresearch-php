<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\Client;
use Manticoresearch\Cluster;
use Manticoresearch\Connection;
use Manticoresearch\Connection\Strategy\Random;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Exceptions\NoMoreNodesException;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Request;
use Manticoresearch\Response;
use Manticoresearch\Response\Token;
use Manticoresearch\Table;
use Manticoresearch\Test\Helper\PopulateHelperTest;
use Manticoresearch\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
	public function testEmptyConfig() {
		$client = new Client();
		$this->assertCount(1, $client->getConnections());
	}

	public function testObjectStrategy() {
		$client = new Client(['connectionStrategy'  => new Connection\Strategy\RoundRobin()]);
		$this->assertCount(1, $client->getConnections());
	}

	public function testClassnameStrategy() {
		$client = new Client(['connectionStrategy'  => 'Connection\Strategy\RoundRobin']);
		$this->assertCount(1, $client->getConnections());
	}

	public function testCluster() {
		$client = new Client();
		$this->assertInstanceOf(Cluster::class, $client->cluster());
	}

	public function testTable(): void {
		$client = new Client();
		$table = $client->table();

		$this->assertInstanceOf(Table::class, $table);
	}

	public function testTableName(): void {
		$client = new Client();
		$table = $client->table('video');

		$this->assertInstanceOf(Table::class, $table);
		$this->assertEquals('video', $table->getName());
	}

	public function testCreationWithConnection() {
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		$connection = new Connection($params);
		$params = ['connections' => $connection];
		$client = new Client($params);
		$this->assertCount(1, $client->getConnections());
	}

	public function testCreationWithConnectionSingularArray() {
		$params = ['host' => $_SERVER['MS_HOST'], 'port' => $_SERVER['MS_PORT']];
		$connection = new Connection($params);
		$params = ['connections' => [$connection]];
		$client = new Client($params);
		$this->assertCount(1, $client->getConnections());
	}

	public function testStrategyConfig() {
		$params = ['connectionStrategy' => 'Random'];
		$client = Client::create($params); //new Client($params);
		$strategy = $client->getConnectionPool()->getStrategy();
		$this->assertInstanceOf(Random::class, $strategy);
	}

	public function testConnectionError() {
		$params = ['host' => '127.0.0.1', 'port' => 9307];
		$client = new Client($params);
		$this->expectException(ConnectionException::class);
		$client->search(['body' => '']);
	}

	public function testConnectionNoMoreRetriesError() {
		$params = [
			'connections' => [
				[
					'host' => '127.0.0.1',
					'port' => 9418,
				],
				[
					'host' => '127.0.0.2',
					'port' => 9428,
				],
			],
			'retries' => 2,
		];
		$exMsg = "After 2 retries to 2 nodes, connection has failed. No more retries left.\n"
			. "Retries made:\n 1. to 127.0.0.2:9428, failure reason: Couldn't connect to server\n"
			. " 2. to 127.0.0.1:9418, failure reason: Couldn't connect to server\n";
		$client = new Client($params);
		$this->expectException(ConnectionException::class);
		$this->expectExceptionMessage($exMsg);
		$client->search(['body' => '']);
	}

	public function testDouble() {
		$params = ['connections' =>
			[
				[
					'host' => '123.0.0.1',
					'port' => '1234',
					'timeout' => 5,
					'connection_timeout' => 1,
					'proxy' => '127.0.0.255',
					'username' => 'test',
					'password' => 'secret',
					'headers' => [
						'X-Forwarded-Host' => 'mydev.domain.com',
					],
					'curl' => [
						CURLOPT_FAILONERROR => true,
					],
					'persistent' => true,
				],
				[
					'host' => '123.0.0.2',
					'port' => '1235',
					'timeout' => 5,
					'transport' => 'Https',
					'curl' => [
						CURLOPT_CAPATH => 'path/to/my/ca/folder',
						CURLOPT_SSL_VERIFYPEER => true,
					],
					'connection_timeout' => 1,
					'persistent' => true,
				],

			],
		];
		$client = new Client($params);
		$this->expectException(ConnectionException::class);
		$client->search(['body' => '']);
	}

	public function testGetLastResponse() {
		$helper = new PopulateHelperTest('testDummy');
		$helper->populateForKeywords();
		$client = $helper->getClient();

		$payload = [
			'body' => [
				'table' => 'products',
				'query' => [
					'match' => ['*' => 'broken'],
				],
			],
		];

		$result = $client->search($payload);
		$lastResponse = $client->getLastResponse()->getResponse();
		$this->assertEquals($result, $lastResponse);
	}
	public function testUnsetLastResponse() {
		$helper = new PopulateHelperTest('testDummy');
		$helper->populateForKeywords();
		$client = $helper->getClient();

		$payload = [
			'body' => [
				'table' => 'products',
				'query' => [
					'match' => ['*' => 'broken'],
				],
			],
		];

		$result = $client->search($payload);
		$lastResponse = $client->getLastResponse()->getResponse();
		$this->assertEquals($result, $lastResponse);

		$client->unsetLastResponse();

		$lastResponse = $client->getLastResponse()->getResponse();
		$this->assertEquals([], $lastResponse);
	}

	public function testTokenEndpointReturnsRawToken() {
		$client = $this->createTokenClient();

		$this->assertSame('raw-token', $client->token());
		$this->assertSame('/token', $client->getTokenRequest()->getPath());
		$this->assertSame('POST', $client->getTokenRequest()->getMethod());
		$this->assertSame('{}', $client->getTokenRequest()->getBody());
		$this->assertSame(
			Token::class,
			$client->getTokenRequestParams()['responseClass']
		);
	}

	public function testTokenEndpointCanReturnResponseObject() {
		$client = $this->createTokenClient();

		$this->assertInstanceOf(Token::class, $client->token(true));
	}

	public function testRetryableResponsesRecover() {
		foreach ([503, 504] as $status) {
			foreach (['<html>timeout</html>', '', '{}', '{"message":"timeout"}', '{"error":"timeout"}'] as $body) {
				$client = $this->createRetryClient(
					[new Response($body, $status), new Response('{"ok":true}', 200)],
					2
				);
				$this->assertSame(['ok' => true], $client->request($this->createRetryRequest())->getResponse());
				$this->assertSame(0, $client->getConnectionPool()->retriesAttempts);
			}
		}
	}

	public function testExhaustedRetriesPreserveStatusesAndLastException() {
		$client = $this->createRetryClient(
			[
				new Response('<html>unavailable</html>', 503),
				new Response('{"error":"proxy: upstream timeout"}', 504),
			],
			2
		);
		$request = $this->createRetryRequest();
		try {
			$client->request($request);
			$this->fail('Expected exhausted retries');
		} catch (NoMoreNodesException $e) {
			$this->assertStringContainsString('HTTP 503: Syntax error', $e->getMessage());
			$this->assertStringContainsString('HTTP 504: "proxy: upstream timeout"', $e->getMessage());
			$this->assertSame(504, $e->getCode());
			$this->assertSame($request, $e->getRequest());
			$this->assertInstanceOf(ResponseException::class, $e->getPrevious());
			$this->assertSame(504, $e->getPrevious()->getResponse()->getStatusCode());
			$this->assertSame($request, $e->getPrevious()->getRequest());
		}
	}

	public function testDisabledRetriesPreserveHttpStatus() {
		foreach ([503, 504] as $status) {
			$client = $this->createRetryClient([new Response('{}', $status)], 0);
			try {
				$client->request($this->createRetryRequest());
				$this->fail('Expected HTTP failure');
			} catch (NoMoreNodesException $e) {
				$this->assertSame('HTTP ' . $status, $e->getMessage());
				$this->assertSame($status, $e->getCode());
				$this->assertInstanceOf(ResponseException::class, $e->getPrevious());
			}
		}
	}

	public function testOtherHttpErrorsAreNotRetried() {
		$client = $this->createRetryClient([new Response('{"error":"invalid query"}', 400)], 2);
		$this->expectException(ResponseException::class);
		$this->expectExceptionMessage('"invalid query"');
		$client->request($this->createRetryRequest());
	}

	private function createRetryClient(array $responses, int $retries): Client {
		$transport = $this->createMock(TransportInterface::class);
		$transport->expects($this->exactly(sizeof($responses)))->method('execute')->willReturnCallback(
			static function (Request $request) use (&$responses) {
				$response = array_shift($responses);
				if ($response->hasError()) {
					throw new ResponseException($request, $response);
				}
				return $response;
			}
		);
		$connection = $this->getMockBuilder(Connection::class)
			->setConstructorArgs([['persistent' => false]])
			->onlyMethods(['getTransportHandler'])
			->getMock();
		$connection->expects($this->exactly(sizeof($responses)))->method('getTransportHandler')->willReturn($transport);
		return new Client(['connections' => [$connection], 'retries' => $retries]);
	}

	private function createRetryRequest(): Request {
		$request = new Request(['body' => []]);
		$request->setPath('/search');
		$request->setMethod('GET');
		return $request;
	}

	private function createTokenClient() {
		return new class extends Client {
			private $tokenRequest;
			private $tokenRequestParams;

			public function __construct() {
			}

			public function request(Request $request, array $params = [], string $retryReason = ''): Response {
				unset($retryReason);
				$this->tokenRequest = $request;
				$this->tokenRequestParams = $params;
				return new Token("raw-token\n", 200);
			}

			public function getTokenRequest() {
				return $this->tokenRequest;
			}

			public function getTokenRequestParams() {
				return $this->tokenRequestParams;
			}
		};
	}
}
