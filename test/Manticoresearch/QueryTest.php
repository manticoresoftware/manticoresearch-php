<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\Query;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
	/** @var Query */
	private $query;

	public function setUp() : void {
		parent::setUp();
		$this->query = new Query();
	}

	public function testNoParams() {
		$this->assertEquals([], $this->query->toArray());
	}

	public function testParamsNoNesting() {
		$this->query->add('a', 1);
		$this->query->add('b', 2);
		$this->query->add('c', 3);
		$this->assertEquals(
			[
			'a' => 1,
			'b' => 2,
			'c' => 3,
			], $this->query->toArray()
		);
	}

	public function testParamsWithNesting() {
		$this->query->add('a', 1);
		$subParams = ['b' => 2, 'c' => 3];
		$this->query->add('x', $subParams);
		$this->assertEquals(
			[
			'a' => 1,
			'x' => [
				'b' => 2,
				'c' => 3,
			],
			], $this->query->toArray()
		);
	}

	public function testParamsWithNull() {
		$this->query->add('a', 1);
		$subParams = ['b' => null];
		$this->query->add('x', $subParams);
		$this->assertEquals(
			[
			'a' => 1,
			'x' => null,
			], $this->query->toArray()
		);
	}

	public function testWithParamsAndSubQuery() {
		$this->query->add('a', 1);
		$subquery = new Query();
		$subquery->add('b', 2);
		$this->query->add('x', $subquery);
		$this->assertEquals(['a' => 1, 'x' => ['b' => 2]], $this->query->toArray());
	}
}
