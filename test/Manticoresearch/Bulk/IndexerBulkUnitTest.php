<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test\Bulk;

use Manticoresearch\Bulk\AdaptiveTuner;
use Manticoresearch\Bulk\NdjsonStream;
use Manticoresearch\Bulk\OperationIterator;
use Manticoresearch\Bulk\OperationNormalizer;
use Manticoresearch\Bulk\Options;
use Manticoresearch\Exceptions\RuntimeException;
use PHPUnit\Framework\TestCase;

class IndexerBulkUnitTest extends TestCase
{
	public function testOptionsDefaults() {
		$opts = Options::fromArray([]);
		$this->assertNull($opts->workers);
		$this->assertSame(1000, $opts->probeDocs);
		$this->assertSame(0.0, $opts->gainThreshold);
		$this->assertFalse($opts->fallback);
	}

	public function testOptionsRejectInvalidWorkers() {
		$this->expectException(RuntimeException::class);
		Options::fromArray(['workers' => 0]);
	}

	public function testNormalizeInsertEnvelope() {
		$normalizer = new OperationNormalizer();
		$payload = $normalizer->normalize(
			[
				'insert' => [
					'table' => 'products',
					'id' => 10,
					'doc' => ['title' => 'Bag'],
				],
			]
		);
		$this->assertSame(
			['table' => 'products', 'id' => 10, 'doc' => ['title' => 'Bag']],
			$payload
		);
	}

	public function testNormalizeDocumentWithExpectedTable() {
		$normalizer = new OperationNormalizer('products');
		$payload = $normalizer->normalize(['id' => 3, 'title' => 'Bag']);
		$this->assertSame(3, $payload['id']);
		$this->assertSame('products', $payload['table']);
		$this->assertSame(['title' => 'Bag'], $payload['doc']);
	}

	public function testRejectZeroId() {
		$normalizer = new OperationNormalizer('products');
		$this->expectException(RuntimeException::class);
		$normalizer->normalize(['id' => 0, 'title' => 'x']);
	}

	public function testRejectNonInsertAction() {
		$normalizer = new OperationNormalizer();
		$this->expectException(RuntimeException::class);
		$normalizer->normalize(['delete' => ['table' => 'products', 'id' => 1]]);
	}

	public function testRejectCluster() {
		$normalizer = new OperationNormalizer('products');
		$this->expectException(RuntimeException::class);
		$normalizer->normalize(
			[
				'insert' => [
					'table' => 'products',
					'cluster' => 'c1',
					'id' => 1,
					'doc' => ['title' => 'x'],
				],
			]
		);
	}

	public function testEncodeLine() {
		$normalizer = new OperationNormalizer();
		$line = $normalizer->encodeLine(
			['table' => 'products', 'id' => 1, 'doc' => ['title' => 'x']]
		);
		$this->assertSame(
			'{"insert":{"table":"products","id":1,"doc":{"title":"x"}}}',
			$line
		);
		$this->assertStringNotContainsString("\n", $line);
	}

	public function testNdjsonStreamSplitsByDocLimit() {
		$normalizer = new OperationNormalizer('t');
		$source = new OperationIterator(
			[
				['id' => 1, 'title' => 'a'],
				['id' => 2, 'title' => 'b'],
				['id' => 3, 'title' => 'c'],
			],
			$normalizer
		);

		$first = NdjsonStream::tryCreate($source, 2);
		$this->assertNotNull($first);
		$body = '';
		while (($chunk = $first->read(64)) !== '') {
			$body .= $chunk;
		}
		$this->assertSame(2, $first->getDocs());
		$this->assertStringEndsWith("\n", $body);
		$this->assertStringNotContainsString("\n\n", $body);

		$second = NdjsonStream::tryCreate($source, 2);
		$this->assertNotNull($second);
		while ($second->read(64) !== '') {
			// drain
		}
		$this->assertSame(1, $second->getDocs());

		$this->assertNull(NdjsonStream::tryCreate($source, 2));
	}

	public function testWorkersDrainSharedSourceUntilEmpty() {
		$normalizer = new OperationNormalizer('t');
		$ops = [];
		for ($i = 1; $i <= 10; $i++) {
			$ops[] = ['id' => $i, 'title' => 'd' . $i];
		}
		$source = new OperationIterator($ops, $normalizer);

		// Two unlimited streams share the iterator (same model as final upload).
		$streams = [];
		for ($i = 0; $i < 2; $i++) {
			$stream = NdjsonStream::tryCreate($source);
			$this->assertNotNull($stream);
			$streams[] = $stream;
		}

		$docs = 0;
		foreach ($streams as $stream) {
			while ($stream->read(128) !== '') {
				// drain
			}
			$docs += $stream->getDocs();
		}
		$this->assertSame(10, $docs);
		$this->assertNull(NdjsonStream::tryCreate($source));
	}

	public function testNdjsonStreamEncodesIncrementally() {
		$normalizer = new OperationNormalizer('t');
		$source = new OperationIterator(
			[
				['id' => 1, 'title' => 'a'],
				['id' => 2, 'title' => 'b'],
			],
			$normalizer
		);
		$stream = NdjsonStream::tryCreate($source, 10);
		$this->assertNotNull($stream);
		// First primed line is buffered; subsequent docs are encoded only as read() needs bytes.
		$firstChunk = $stream->read(1);
		$this->assertSame(1, strlen($firstChunk));
		$this->assertSame(1, $stream->getDocs());
		while ($stream->read(1024) !== '') {
			// drain remaining including second document
		}
		$this->assertSame(2, $stream->getDocs());
	}

	public function testNdjsonStreamRejectsDuplicateIdsInRequest() {
		$normalizer = new OperationNormalizer('t');
		$source = new OperationIterator(
			[
				['id' => 1, 'title' => 'a'],
				['id' => 1, 'title' => 'b'],
			],
			$normalizer
		);
		$stream = NdjsonStream::tryCreate($source, 10);
		$this->assertNotNull($stream);
		$this->expectException(RuntimeException::class);
		while ($stream->read(1024) !== '') {
			// drain until duplicate is pulled
		}
	}

	public function testAdaptiveTunerCandidates() {
		$tuner = new AdaptiveTuner(0.05, 10);
		$this->assertSame([1, 2, 4, 8, 10], $tuner->candidates());
	}

	public function testAdaptiveTunerStopsWithoutGain() {
		$tuner = new AdaptiveTuner(0.10, 8);
		$calls = [];
		$result = $tuner->tune(
			static function ($workers) use (&$calls) {
				$calls[] = $workers;
				// 1 -> 100, 2 -> 105 (<10% gain), should stop before 4
				$map = [1 => 100.0, 2 => 105.0, 4 => 200.0, 8 => 400.0];
				return $map[$workers];
			}
		);
		$this->assertSame([1, 2], $calls);
		$this->assertSame(2, $result['selected_workers']);
	}

	public function testAdaptiveTunerSelectsBestOnImprovement() {
		$tuner = new AdaptiveTuner(0.05, 4);
		$result = $tuner->tune(
			static function ($workers) {
				$map = [1 => 100.0, 2 => 180.0, 4 => 300.0];
				return $map[$workers];
			}
		);
		$this->assertSame(4, $result['selected_workers']);
		$this->assertCount(3, $result['stages']);
	}
}
