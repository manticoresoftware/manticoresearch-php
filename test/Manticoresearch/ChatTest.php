<?php

// Copyright (c) Manticore Software LTD (https://manticoresearch.com)
//
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

namespace Manticoresearch\Test;

use Manticoresearch\ChatResult;
use Manticoresearch\Client;
use Manticoresearch\Response;
use Manticoresearch\Search;
use PHPUnit\Framework\TestCase;

class ChatTest extends TestCase
{
	public function testChatPayloadWithRequiredFields(): void {
		$search = new Search(new Client());
		$search->chat('What is vector search?', 'docs', 'assistant');

		$this->assertSame(
			[
				'chat' => [
					'query' => 'What is vector search?',
					'table' => 'docs',
					'model_name' => 'assistant',
				],
			],
			$search->compile()
		);
	}

	public function testChatPayloadWithAllFields(): void {
		$search = new Search(new Client());
		$search->setTable('ignored')
			->chat('What is vector search?', 'docs', 'assistant', 'docs-chat-001', 'embedding');

		$this->assertSame(
			[
				'chat' => [
					'query' => 'What is vector search?',
					'table' => 'docs',
					'model_name' => 'assistant',
					'conversation_uuid' => 'docs-chat-001',
					'vector_field' => 'embedding',
				],
			],
			$search->compile()
		);
	}

	public function testChatCannotBeCombinedWithQuery(): void {
		$search = new Search(new Client());
		$search->chat('Question', 'docs', 'assistant')->search('vector search');

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Chat search cannot be combined with a query');
		$search->compile();
	}

	public function testChatCannotBeCombinedWithKnn(): void {
		$search = new Search(new Client());
		$search->knn('embedding', [0.1, 0.2], 10)->chat('Question', 'docs', 'assistant');

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Chat search cannot be combined with KNN search');
		$search->compile();
	}

	public function testChatCannotBeCombinedWithHybridSearch(): void {
		$search = new Search(new Client());
		$search->hybrid('vector search')->chat('Question', 'docs', 'assistant');

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Chat search cannot be combined with hybrid search');
		$search->compile();
	}

	public function testChatResultProvidesResponseFields(): void {
		$responseBody = json_encode(
			[
				'conversation_uuid' => 'docs-chat-001',
				'user_query' => 'What is vector search?',
				'search_query' => 'vector search embeddings',
				'response' => 'Vector search compares embeddings.',
				'sources' => '[{"id":1,"knn_dist":0.12}]',
			]
		);
		$response = new Response(
			$responseBody
		);
		$result = new ChatResult($response);

		$this->assertSame('docs-chat-001', $result->getConversationUuid());
		$this->assertSame('What is vector search?', $result->getUserQuery());
		$this->assertSame('vector search embeddings', $result->getSearchQuery());
		$this->assertSame('Vector search compares embeddings.', $result->getResponse());
		$this->assertSame('Vector search compares embeddings.', $result->getAnswer());
		$this->assertSame([['id' => 1, 'knn_dist' => 0.12]], $result->getSources());
		$this->assertSame('[{"id":1,"knn_dist":0.12}]', $result->getRawSources());
		$this->assertSame($response->getResponse(), $result->getData());
		$this->assertSame($response, $result->getResponseObject());
	}

	public function testGetSendsChatPayloadAndReturnsChatResult(): void {
		$response = new Response(
			[
				'conversation_uuid' => 'generated-id',
				'response' => 'Answer',
				'sources' => '[]',
			]
		);
		$client = new class ($response) extends Client {
			private $chatResponse;
			private $searchParams;
			private $objectResponseRequested;

			public function __construct(Response $response) {
				$this->chatResponse = $response;
			}

			public function search(array $params = [], $obj = false) {
				$this->searchParams = $params;
				$this->objectResponseRequested = $obj;
				return $this->chatResponse;
			}

			public function getSearchParams() {
				return $this->searchParams;
			}

			public function isObjectResponseRequested() {
				return $this->objectResponseRequested;
			}
		};

		$result = (new Search($client))->chat('Question', 'docs', 'assistant')->get();

		$this->assertSame(
			[
				'body' => [
					'chat' => [
						'query' => 'Question',
						'table' => 'docs',
						'model_name' => 'assistant',
					],
				],
			],
			$client->getSearchParams()
		);
		$this->assertTrue($client->isObjectResponseRequested());
		$this->assertInstanceOf(ChatResult::class, $result);
		$this->assertSame('Answer', $result->getAnswer());
	}
}
