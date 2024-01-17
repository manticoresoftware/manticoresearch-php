<?php


namespace Manticoresearch;

/**
 * Class Request
 * @package Manticoresearch
 */
class Request
{
	/**
	 * @var string
	 */
	protected $path;
	/**
	 * @var string
	 */
	protected $method;
	/**
	 * @var array|string
	 */
	protected $body;
	/**
	 * @var string
	 */
	protected $query;
	/**
	 * @var string
	 */
	protected $contentType;

	public function __construct($params = []) {
		if (sizeof($params) <= 0) {
			return;
		}

		$this->setBody($params['body'] ?? []);
		$this->setQuery($params['query'] ?? []);
		$this->setContentType($params['content_type'] ?? 'application/json');
	}

	/**
	 * @return mixed
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @param mixed $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	/**
	 * @return mixed
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @param mixed $body
	 */

	public function setBody($body = null) {
		$this->body = $body;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @param mixed $method
	 */
	public function setMethod($method) {
		$this->method = $method;
	}

	/**
	 * @return mixed
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * @param mixed $content_type
	 */
	public function setContentType($contentType) {
		$this->contentType = $contentType;
	}

	/**
	 * @return mixed
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @param mixed $query
	 */
	public function setQuery($query) {
		$this->query = $query;
	}
	/*
	 * #return string
	 */
	public function toArray() {
		return [
			'path' => $this->getPath(),
			'method' => $this->getMethod(),
			'content_type' => $this->getContentType(),
			'query' => $this->getQuery(),
			'body' => $this->getBody(),
		];
	}
}
