<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch;

use Manticoresearch\Exceptions\RuntimeException;
use Psr\Log\LoggerInterface;

/**
 * Class Connection
 * @package Manticoresearch
 */
class CurlConnection extends Connection
{
	/**
	 * @var resource
	 */
	protected static $sharedCurl;
	/**
	 * @var resource
	 */
	protected $curl;

/*
 * $params['transport']  = transport class name
 * $params['host']       = hostname
 * $params['path']       = path
 * $params['port']       = port number
 * $params['timeout']    = connection timeout
 * $params['connect_timeout'] = connection connect timeout
 * $params['proxy']       = proxy host:port string
 * $params['username']  = username for http auth
 * $params['password']  = password for http auth
 * $params['headers']   = array of custom headers
 * $params['curl']      = array of pairs of curl option=>value
 * $params['persistent'] = bool if connection is persistent
 * $params['shared'] = bool if connection is shared between client instances
 */
	/**
	 * CurlConnection constructor.
	 * @param array $params
	 */
	public function __construct(array $params) {
		parent::__construct($params);
		if (!$this->config['persistent']) {
			return;
		}
		if ($this->config['persistent'] === 1) {
			$this->curl = curl_init();
		} elseif (!isset(static::$curl)) {
			static::$sharedCurl = curl_init();
		}
	}

	/**
	 * @return resource|null
	 *
	 */
	public function getCurl() {
		return static::$sharedCurl ?? ($this->curl ?? curl_init());
	}
	
}
