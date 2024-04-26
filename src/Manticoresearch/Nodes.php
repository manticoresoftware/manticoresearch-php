<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch;

use Manticoresearch\Endpoints\Nodes\AgentStatus;
use Manticoresearch\Endpoints\Nodes\CreateFunction;
use Manticoresearch\Endpoints\Nodes\CreatePlugin;
use Manticoresearch\Endpoints\Nodes\Debug;
use Manticoresearch\Endpoints\Nodes\DropFunction;
use Manticoresearch\Endpoints\Nodes\DropPlugin;
use Manticoresearch\Endpoints\Nodes\FlushAttributes;
use Manticoresearch\Endpoints\Nodes\FlushHostnames;
use Manticoresearch\Endpoints\Nodes\FlushLogs;
use Manticoresearch\Endpoints\Nodes\Plugins;
use Manticoresearch\Endpoints\Nodes\ReloadIndexes;
use Manticoresearch\Endpoints\Nodes\ReloadPlugins;
use Manticoresearch\Endpoints\Nodes\Set;
use Manticoresearch\Endpoints\Nodes\Status;
use Manticoresearch\Endpoints\Nodes\Tables;
use Manticoresearch\Endpoints\Nodes\Threads;
use Manticoresearch\Endpoints\Nodes\Variables;
use Manticoresearch\Response\SqlToArray;

class Nodes
{
	/**
	 * @var Client
	 */
	protected $client;
	protected $params = ['responseClass' => SqlToArray::class];

	/**
	 * Nodes namespace
	 * @param Client $client
	 */
	public function __construct($client) {
		$this->client = $client;
	}

	public function agentstatus($params = []) {
		$body = $params['body'] ?? [];
		$endpoint = new AgentStatus();
		$endpoint->setBody($body);
		$response = $this->client->request(
			$endpoint,
			array_merge($this->params, ['responseClassParams' => ['customMapping' => true]])
		);
		return  $response->getResponse();
	}

	public function createfunction($params) {
		$body = $params['body'];
		$endpoint = new CreateFunction();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return  $response->getResponse();
	}

	public function createplugin($params) {
		$body = $params['body'];
		$endpoint = new CreatePlugin();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return  $response->getResponse();
	}

	public function debug($params) {
		$body = $params['body'];
		$endpoint = new Debug();
		$endpoint->setBody($body);
		$response = $this->client->request(
			$endpoint,
			array_merge($this->params, ['responseClassParams' => ['customMapping' => true]])
		);
		return  $response->getResponse();
	}

	public function dropfunction($params) {
		$body = $params['body'];
		$endpoint = new DropFunction();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return  $response->getResponse();
	}

	public function dropplugin($params) {
		$body = $params['body'];
		$endpoint = new DropPlugin();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return  $response->getResponse();
	}

	public function flushattributes($params = []) {
		$body = $params['body'] ?? [];
		$endpoint = new FlushAttributes();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, ['responseClass' => Response\Sql::class]);
		return  $response->getResponse();
	}

	public function flushhostnames($params = []) {
		$body = $params['body'] ?? [];
		$endpoint = new FlushHostnames();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return  $response->getResponse();
	}

	public function flushlogs($params = []) {
		$body = $params['body'] ?? [];
		$endpoint = new FlushLogs();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return  $response->getResponse();
	}

	public function plugins($params = []) {
		$body = $params['body'] ?? [];
		$endpoint = new Plugins();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return  $response->getResponse();
	}

	public function reloadindexes($params = []) {
		$body = $params['body'] ?? [];
		$endpoint = new ReloadIndexes();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return  $response->getResponse();
	}

	public function reloadplugins($params = []) {
		$body = $params['body'] ?? [];
		$endpoint = new ReloadPlugins();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return  $response->getResponse();
	}

	public function set($params) {
		$body = $params['body'];
		$endpoint = new Set();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return  $response->getResponse();
	}

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function status($params = []) {
		$body = $params['body'] ?? [];
		$endpoint = new Status();
		$endpoint->setBody($body);
		$response = $this->client->request(
			$endpoint,
			array_merge($this->params, ['responseClassParams' => ['customMapping' => true]])
		);
		return  $response->getResponse();
	}

	public function tables($params = []) {
		$body = $params['body'] ?? [];
		$endpoint = new Tables();
		$endpoint->setBody($body);
		$response = $this->client->request(
			$endpoint,
			array_merge($this->params, ['responseClassParams' => ['customMapping' => true]])
		);
		return  $response->getResponse();
	}

	public function threads($params = []) {
		$body = $params['body'] ?? [];
		$endpoint = new Threads();
		$endpoint->setBody($body);
		$response = $this->client->request($endpoint, $this->params);
		return  $response->getResponse();
	}

	public function variables($params = []) {
		$body = $params['body'] ?? [];
		$endpoint = new Variables();
		$endpoint->setBody($body);
		$response = $this->client->request(
			$endpoint,
			array_merge($this->params, ['responseClassParams' => ['customMapping' => true]])
		);
		return  $response->getResponse();
	}
}
