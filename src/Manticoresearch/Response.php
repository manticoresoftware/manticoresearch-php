<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch;

/**
 * Manticore response object
 *  Stores result array, time and errors
 * @category ManticoreSearch
 * @package ManticoreSearch
 * @author Adrian Nuta <adrian.nuta@manticoresearch.com>
 * @link https://manticoresearch.com
 */
use Manticoresearch\Exceptions\RuntimeException;

/**
 * Class Response
 * @package Manticoresearch
 */
class Response
{
	/**
	 * execution time to get the response
	 * @var integer|float
	 */
	protected $time;

	/**
	 * raw response as string
	 * @var string
	 */
	protected $string;

	/**
	 * information about request
	 * @var array
	 */
	protected $transportInfo;

	protected $status;
	/**
	 * response as array
	 * @var array
	 */
	protected $response;

	/**
	 * additional params as array
	 * @var array
	 */
	protected $params;


	public function __construct($responseString, $status = null, $params = []) {
		if (is_array($responseString)) {
			$this->response = $responseString;
		} else {
			$this->string = $responseString;
		}
		$this->status = $status;
		$this->params = $params;
	}

	/*
	 * Return response
	 * @return array
	 */
	public function getResponse() {
		if (null === $this->response) {
			$this->response = json_decode($this->string, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				if (json_last_error() !== JSON_ERROR_UTF8 || !$this->stripBadUtf8()) {
					throw new RuntimeException('fatal error while trying to decode JSON response: '
						. json_last_error_msg());
				}

				$this->response = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $this->string), true);
			}

			if (empty($this->response)) {
				$this->response = [];
			}
		}
		return $this->response;
	}

	/**
	 * check if strip_bad_utf8 as been set to true
	 * @return boolean
	 */
	private function stripBadUtf8() {
		return !empty($this->transportInfo['body']) && !empty($this->transportInfo['body']['strip_bad_utf8']);
	}

	/*
	 * Check whenever response has error
	 * @return bool
	 */
	public function hasError() {
		$response = $this->getResponse();
		if (is_array($response)) {
			foreach ($response as $r) {
				if (isset($r['error']) && $r['error'] !== '') {
					return true;
				}
			}
		}
		return (isset($response['error']) && $response['error'] !== '') ||
			(isset($response['errors']) && $response['errors'] !== false);
	}

	/*
	 * Return error
	 * @return false|string
	 */
	public function getError() {
		$response = $this->getResponse();
		if (isset($response['error'])) {
			return json_encode($response['error'], true);
		}

		if (isset($response['errors'])) {
			return json_encode($response['errors'], true);
		}

		if (is_array($response)) {
			$errors = '';
			foreach ($response as $r) {
				if (!isset($r['error']) || $r['error'] === '') {
					continue;
				}

				$errors .= json_encode($r['error'], true);
			}
			return $errors;
		}

		return '';
	}

	/*
	 * set execution time
	 * @param int|float $time
	 * @return $this
	 */
	public function setTime($time) {
		$this->time = $time;
		return $this;
	}

	/*
	 * returns execution time
	 * @return mixed
	 */
	public function getTime() {
		return $this->time;
	}

	/**
	 *  set request info
	 * @param array $info
	 * @return $this
	 */
	public function setTransportInfo($info) {
		$this->transportInfo = $info;
		return $this;
	}

	/**
	 * get request info
	 * @return array
	 */
	public function getTransportInfo() {
		return $this->transportInfo;
	}
}
