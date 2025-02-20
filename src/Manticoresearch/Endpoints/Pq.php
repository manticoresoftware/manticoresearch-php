<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Endpoints;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Pq\DeleteByQuery;
use Manticoresearch\Endpoints\Pq\Doc;

/**
 * Class Pq
 * @package Manticoresearch\Endpoints
 */
class Pq
{
	/**
	 * @var Client
	 */
	protected $client;

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
	public function doc($params) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$id = $params['id'] ?? null;

		$body = $params['body'];
		$endpoint = new Doc();
		$endpoint->setTable($table);
		$endpoint->setId($id);
		$endpoint->setQuery($params['query'] ?? null);
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint);
		return $response->getResponse();
	}

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function search($params, $obj = false) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$body = $params['body'];
		$endpoint = new \Manticoresearch\Endpoints\Pq\Search();
		$endpoint->setTable($table);
		$endpoint->setQuery($params['query'] ?? null);
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint);
		if ($obj === true) {
			return $response;
		}

		return $response->getResponse();
	}

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function deleteByQuery($params = []) {
		$table = $params['table'] ?? $params['index'] ?? null;
		$body = $params['body'];
		$endpoint = new DeleteByQuery();
		$endpoint->setTable($table);
		$endpoint->setQuery($params['query'] ?? null);
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint);
		return $response->getResponse();
	}
}
