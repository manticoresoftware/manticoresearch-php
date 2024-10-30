<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;

class SearchTest extends \PHPUnit\Framework\TestCase
{
	public function testEmptyBody() {
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		$client = new Client($params);
		$result = $client->search(['body' => '']);
		$this->assertEquals([['total' => 0, 'error' => '', 'warning' => '']], $result);
	}

	public function testNoArrayParams() {
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		$client = new Client($params);
		$this->expectException(\TypeError::class);
		$client->search('this is not a json');
	}

	public function testMissingIndex() {
		$params = [
			'host' => $_SERVER['MS_HOST'],
			'port' => $_SERVER['MS_PORT'],
			'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT'],
		];
		$client = new Client($params);
		$this->expectException(\Manticoresearch\Exceptions\ResponseException::class);
		$client->search(
			[
			'body' => [

				'query' => [
					'match_phrase' => [
						'title' => 'find me',
					],
				],
			],
			]
		);
	}

	public function testPath() {
		$search = new \Manticoresearch\Endpoints\Search();
		$this->assertEquals('/search', $search->getPath());
	}
}
