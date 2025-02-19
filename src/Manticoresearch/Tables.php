<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch;

use Manticoresearch\Endpoints\Tables\Alter;
use Manticoresearch\Endpoints\Tables\Create;
use Manticoresearch\Endpoints\Tables\Describe;
use Manticoresearch\Endpoints\Tables\Drop;
use Manticoresearch\Endpoints\Tables\FlushRamchunk;
use Manticoresearch\Endpoints\Tables\FlushRttable;
use Manticoresearch\Endpoints\Tables\Import;
use Manticoresearch\Endpoints\Tables\Optimize;
use Manticoresearch\Endpoints\Tables\Settings;
use Manticoresearch\Endpoints\Tables\Status;
use Manticoresearch\Endpoints\Tables\Truncate;
use Manticoresearch\Response\SqlToArray;

class Tables
{
	use Utils;
	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * @var array
	 */
	protected $params = ['responseClass' => SqlToArray::class];

	/**
	 * Pq constructor.
	 * @param Client $client
	 */
	public function __construct($client) {
		$this->client = $client;
	}

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function alter($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$body = $params['body'];
		$endpoint = new Alter();
		$endpoint->setTable($table);
		$endpoint->setBody($body);
		$response = $this->client->request(
			$endpoint,
			array_merge($this->params, ['responseClassParams' => ['customMapping' => true]])
		);
		return $response->getResponse();
	}


	/**
	 *
	 * @param array $params
	 * @return mixed
	 */
	public function create($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$body = $params['body'];
		$endpoint = new Create();
		$endpoint->setTable($table);
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return $response->getResponse();
	}

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function describe($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$body = $params['body'] ?? [];
		$endpoint = new Describe();
		$endpoint->setTable($table);
		$endpoint->setBody($body);
		$response = $this->client->request(
			$endpoint,
			array_merge($this->params, ['responseClassParams' => ['customMapping' => true]])
		);
		return $response->getResponse();
	}

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function drop($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$body = $params['body'] ?? [];
		$endpoint = new Drop();
		$endpoint->setTable($table);
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return $response->getResponse();
	}
	/**
	 * @param array $params
	 * @return mixed
	 */
	public function import($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$body = $params['body'] ?? [];
		$endpoint = new Import();
		$endpoint->setTable($table);
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return $response->getResponse();
	}
	/**
	 * @param array $params
	 * @return mixed
	 */
	public function flushramchunk($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$endpoint = new FlushRamchunk();
		$endpoint->setTable($table);
		$endpoint->setBody();
		$response = $this->client->request($endpoint, $this->params);
		return $response->getResponse();
	}

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function flushrttable($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$endpoint = new FlushRttable();
		$endpoint->setTable($table);
		$endpoint->setBody();
		$response = $this->client->request($endpoint, $this->params);
		return $response->getResponse();
	}

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function optimize($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$body = $params['body'] ?? null;
		$endpoint = new Optimize();
		$endpoint->setTable($table);
		$endpoint->setBody($body);
		$response = $this->client->request(
			$endpoint,
			array_merge($this->params, ['responseClassParams' => ['customMapping' => true]])
		);
		return $response->getResponse();
	}

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function status($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$body = $params['body'] ?? null;
		$endpoint = new Status();
		$endpoint->setTable($table);
		$endpoint->setBody($body);
		$response = $this->client->request(
			$endpoint,
			array_merge($this->params, ['responseClassParams' => ['customMapping' => true]])
		);
		return $response->getResponse();
	}

	/**
	 * @param array $params
	 * @return array|mixed|string
	 */
	public function settings($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$body = $params['body'] ?? null;
		$endpoint = new Settings();
		$endpoint->setTable($table);
		$endpoint->setBody($body);
		$response = $this->client->request(
			$endpoint,
			array_merge($this->params, ['responseClassParams' => ['customMapping' => true]])
		);
		return $response->getResponse();
	}

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function truncate($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$body = $params['body'] ?? null;
		$endpoint = new Truncate();
		$endpoint->setTable($table);
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return $response->getResponse();
	}
}
