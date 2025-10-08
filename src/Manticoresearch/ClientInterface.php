<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

declare(strict_types=1);

namespace Manticoresearch;

use Manticoresearch\Connection\ConnectionPool;

/**
 * Interface for Manticore Search client
 * @package Manticoresearch
 * @category Manticoresearch
 * @link https://manticoresearch.com
 */
interface ClientInterface
{
	/**
	 * Set hosts configuration
	 * @param string|array $hosts
	 */
	public function setHosts($hosts);

	/**
	 * Set client configuration
	 * @param array $config
	 * @return $this
	 */
	public function setConfig(array $config): self;

	/**
	 * Create client instance from configuration
	 * @param array $config
	 * @return Client
	 */
	public static function create($config): Client;

	/**
	 * Create client instance from array configuration
	 * @param array $config
	 * @return Client
	 */
	public static function createFromArray($config): Client;

	/**
	 * Get connections
	 * @return mixed
	 */
	public function getConnections();

	/**
	 * Get connection pool
	 * @return ConnectionPool
	 */
	public function getConnectionPool(): ConnectionPool;

	/**
	 * Endpoint: search
	 * @param array $params
	 * @param bool $obj
	 * @return array|Response
	 */
	public function search(array $params = [], $obj = false);

	/**
	 * Endpoint: insert
	 * @param array $params
	 * @return mixed
	 */
	public function insert(array $params = []);

	/**
	 * Endpoint: replace
	 * @param array $params
	 * @return mixed
	 */
	public function replace(array $params = []);

	/**
	 * Endpoint: _update
	 * @param string $table
	 * @param int $id
	 * @param array $params
	 * @return mixed
	 */
	public function partialReplace(string $table, int $id, array $params = []);

	/**
	 * Endpoint: update
	 * @param array $params
	 * @return mixed
	 */
	public function update(array $params = []);

	/**
	 * Endpoint: sql
	 * @param mixed $params
	 * @return mixed
	 */
	public function sql(...$params);

	/**
	 * Endpoint: delete
	 * @param array $params
	 * @return mixed
	 */
	public function delete(array $params = []);

	/**
	 * Endpoint: pq
	 * @return \Manticoresearch\Endpoints\Pq
	 */
	public function pq(): \Manticoresearch\Endpoints\Pq;

	/**
	 * Endpoint: tables
	 * @return Tables
	 */
	public function tables(): Tables;

	/**
	 * Endpoint: nodes
	 * @return Nodes
	 */
	public function nodes(): Nodes;

	/**
	 * Endpoint: cluster
	 * @return Cluster
	 */
	public function cluster(): Cluster;

	/**
	 * Return Table object
	 * @param string|null $name Name of table
	 * @return Table
	 */
	public function table(?string $name = null): Table;

	/**
	 * Endpoint: bulk
	 * @param array $params
	 * @return mixed
	 */
	public function bulk(array $params = []);

	/**
	 * Endpoint: suggest
	 * @param array $params
	 * @return mixed
	 */
	public function suggest(array $params = []);

	/**
	 * Endpoint: qsuggest
	 * @param array $params
	 * @return mixed
	 */
	public function qsuggest(array $params = []);

	/**
	 * Endpoint: keywords
	 * @param array $params
	 * @return mixed
	 */
	public function keywords(array $params = []);

	/**
	 * Endpoint: autocomplete
	 * @param array $params
	 * @return mixed
	 */
	public function autocomplete(array $params = []);

	/**
	 * Endpoint: explain query
	 * @param array $params
	 * @return mixed
	 */
	public function explainQuery(array $params = []);

	/**
	 * Execute request
	 * @param Request $request
	 * @param array $params
	 * @param string $retryReason
	 * @return Response
	 */
	public function request(Request $request, array $params = [], string $retryReason = ''): Response;

	/**
	 * Get last response
	 * @return Response
	 */
	public function getLastResponse(): Response;

	/**
	 * Unset last response
	 * @return void
	 */
	public function unsetLastResponse(): void;
}
