<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch;

trait Utils
{
	public static function escape($string): string {
		$from = ['\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=', '<'];
		$to = ['\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=', '\<'];
		return str_replace($from, $to, $string);
	}
}
