<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Response;

use Manticoresearch\Response;

class SqlToArray extends Response
{
	public function getResponse() {
		$response = parent::getResponse();
		// workaround for the change in Manticore Search made in 4.2.1, after
		// which any query in mode=raw returns an array
		if (is_array($response)
		and sizeof($response) === 1
		and isset($response[0]['total'], $response[0]['error'], $response[0]['warning'])) {
			foreach ($response[0] as $k => $v) {
				$response[$k] = $v;
			}
			unset($response[0]);
		}

		if (isset($response['columns'], $response['data'])) {
			$data = [];
			array_walk(
				$response['columns'], static function (&$value) {
					$value = array_keys($value)[0];
				}
			);
			$id = -1;
			foreach ($response['data'] as $property) {
				if (isset($property['id'])) {
					$id = $property['id'];
					if (sizeof($property) > 1) {
						unset($property['id']);
					}
				} elseif (isset($this->params['customMapping']) and $this->params['customMapping']) {
					if (isset($property['Field'])) {
						$id = $property['Field'];
						if (sizeof($property) > 1) {
							unset($property['Field']);
						}
					} elseif (isset($property['Variable_name'])) {
						$id = $property['Variable_name'];
						if (sizeof($property) > 1) {
							unset($property['Variable_name']);
						}
					} elseif (isset($property['Index']) || isset($property['Table'])) {
						$id = $property['Index'] ?? $property['Table'];
						if (sizeof($property) > 1) {
							if (isset($property['Index'])) {
								unset($property['Index']);
							} else {
								unset($property['Table']);
							}
						}
					} elseif (isset($property['Counter'])) {
						$id = $property['Counter'];
						if (sizeof($property) > 1) {
							unset($property['Counter']);
						}
					} elseif (isset($property['Key'])) {
						$id = $property['Key'];
						if (sizeof($property) > 1) {
							unset($property['Key']);
						}
					} elseif (isset($property['command'])) {
						$id = $property['command'];
						if (sizeof($property) > 1) {
							unset($property['command']);
						}
					} elseif (isset($property['suggest'])) {
						$id = $property['suggest'];
						if (sizeof($property) > 1) {
							unset($property['suggest']);
						}
					} elseif (isset($property['Variable'])) {
						$id = $property['Variable'];
						if (sizeof($property) > 1) {
							unset($property['Variable']);
						}
					} else {
						$id++;
					}
				} else {
					$id++;
				}
				$data[$id] = (sizeof($property) === 1) ? array_shift($property) : $property;
			}
			return (sizeof($data) > 0) ? $data : [];
		}
		return $response;
	}
}
