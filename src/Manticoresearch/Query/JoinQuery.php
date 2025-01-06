<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Query;

use Manticoresearch\Query;

class JoinQuery extends Query
{
	const JOIN_TYPES = ['inner', 'left'];
	const LEFT_FIELD_TYPES = ['int', 'string'];
	const JOIN_QUERY_TYPES = [
		'Manticoresearch\Query\MatchPhrase',
		'Manticoresearch\Query\MatchQuery',
		'Manticoresearch\Query\QueryString',
	];

	public function __construct(
		$joinType,
		$joinTable,
		$joinLeftField,
		$joinRightField,
		$joinLeftFieldType = '',
		$joinQuery = ''
	) {
		$this->checkJoinOptions($joinType, $joinLeftFieldType, $joinQuery);

		$this->params['type'] = $joinType;
		$this->params['table'] = $joinTable;
		if ($joinQuery) {
			$this->params['query'] = $joinQuery;
		}
		$joinLeft = [
			'field' => $joinLeftField,
		];
		if ($joinLeftFieldType) {
			$joinLeft['type'] = $joinLeftFieldType;
		}
		$joinRight = [
			'field' => $joinRightField,
			'table' => $joinTable,
		];

		$this->params['on'] = [
			[
				'left' => $joinLeft,
				'operator' => 'eq',
				'right' => $joinRight,
			],
		];
	}

	protected function checkJoinOptions($joinType, $joinLeftFieldType, $joinQuery) {
		if (!in_array($joinType, static::JOIN_TYPES)) {
			throw new \RuntimeException("Unknown join type `{$joinType}` passed");
		}
		if ($joinLeftFieldType && !in_array($joinLeftFieldType, static::LEFT_FIELD_TYPES)) {
			throw new \RuntimeException("Unknown join field type `{$joinLeftFieldType}` passed");
		}
		if (!$joinQuery) {
			return;
		}
		foreach (static::JOIN_QUERY_TYPES as $queryType) {
			if (is_a($joinQuery, $queryType)) {
				return;
			}
		}
		throw new \RuntimeException('`joinQuery` must be a full-text query object');
	}

	public function add($k, $v) {
		if ($k === 'main_table') {
			foreach ($this->params['on'] as &$joinOn) {
				$joinOn['left']['table'] = $v;
			}
		} else {
			parent::add($k, $v);
		}
	}
}
