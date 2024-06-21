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
		$this->curl = curl_init();
	}

	/**
	 * @return resource|null
	 *
	 */
	public function getCurl() {
		return $this->curl ?? curl_init();
	}
	
}
