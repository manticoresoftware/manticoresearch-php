<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Transport;

/**
 * Class Https
 * @package Manticoresearch\Transport
 */
class Https extends Http
{
	/**
	 * @var string
	 */
	protected $scheme = 'https';
}
