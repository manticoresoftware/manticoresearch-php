<?php


namespace Manticoresearch\Test;

use Manticoresearch\Client;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Index;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\MatchQuery;
use Manticoresearch\Query\Range;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    protected $index;

    protected function getIndex($keywords = false): Index
    {
        $params = [
            'host' => $_SERVER['MS_HOST'],
            'port' => $_SERVER['MS_PORT'],
            'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT']
        ];
        $this->index = new Index(new Client($params));
        $this->index->setName('testindex');
        $this->index->drop(true);

        $options = [];
        if ($keywords === true) {
            $options = [
                'dict' => 'keywords',
                'min_infix_len' => 2
            ];
        }

        // for coverage purposes, does not affect functionality as index already dropped silently
        $options['silent'] = true;

        $this->index->create([
            'title' => ['type' => 'text'],
            'gid' => ['type' => 'int'],
            'label' => ['type' => 'string'],
            'tags' => ['type' => 'multi'],
            'props' => ['type' => 'json']
        ], $options);
        return $this->index;
    }

    protected function addDocument($index)
    {
        $index->addDocument([
            'title' => 'This is an example document for testing',
            'gid' => 1,
            'label' => 'not used',
            'tags' => [1, 2, 3],
            'props' => [
                'color' => 'blue',
                'rule' => ['one', 'two']
            ]
        ], 1);
    }


    public function testReplaceDocument()
    {
        $index = $this->getIndex();
        $this->addDocument($index);
        $response = $index->replaceDocument([
            'title' => 'This is an example document for cooking',
            'gid' => 1,
            'label' => 'not used',
            'tags' => [1, 2, 3],
            'props' => [
                'color' => 'blue',
                'rule' => ['one', 'two']
            ]
        ], 1);

        $this->assertEquals([
            '_index' => 'testindex',
            '_id' => 1,
            'created' => false,
            'result' => 'updated',
            'status' => 200,
        ], $response);
    }

    public function testReplaceDocuments()
    {
        $index = $this->getIndex();
        $this->addDocument($index);
        $response = $index->replaceDocuments([[
            'id' => 1,
            'title' => 'This is an example document for cooking',
            'gid' => 1,
            'label' => 'not used',
            'tags' => [1, 2, 3],
            'props' => [
                'color' => 'blue',
                'rule' => ['one', 'two']
            ]
        ]]);

        $this->assertEquals([
            'items' => [
                ['bulk' => [
                    '_index' => 'testindex',
                    '_id' => 1,
                    'created' => 1,
                    'deleted' => 1,
                    'updated' => 0,
                    'result' => 'updated',
                    'status' => 200
                ]]
            ],
            'errors' => false,
            'current_line' => 2,
            'skipped_lines' => 0,
            'error' => ''
        ], $response);
    }

    public function testDeleteDocumentsByIds()
    {
        $index = $this->getIndex();

        $index->addDocuments([
            ['id' => 1, 'title' => 'First document'],
            ['id' => 2, 'title' => 'Second document'],
            ['id' => 3, 'title' => 'Third document'],
            ['id' => 4, 'title' => 'Fourth document'],
            ['id' => 5, 'title' => 'Fifth document'],
        ]);

        $index->deleteDocumentsByIds([2, 4]);
        $documents = $index->getDocumentByIds([1, 2, 3, 4, 5]);
        $remainingIds = [];
        foreach ($documents as $document) {
            $remainingIds[] = $document->getId();
        }
        $this->assertEquals([1, 3, 5], $remainingIds);
    }

    public function testClassOfHit()
    {
        $index = $this->getIndex();
        $this->addDocument($index);
        $hit = $index->getDocumentById(1);
        $this->assertInstanceOf('Manticoresearch\ResultHit', $hit);
    }

    public function testClassOfNonExistentHit()
    {
        $index = $this->getIndex();
        $this->addDocument($index);
        $hit = $index->getDocumentById(2);
        $this->assertNull($hit);
    }

    public function testUpdateTagsThenDeleteDocument()
    {
        $index = $this->getIndex();
        $this->addDocument($index);
        $update = $index->updateDocument(['tags' => [10, 12, 14]], 1);
        $this->assertEquals($update['_id'], 1);

        $index->deleteDocument(1);
        $this->assertEquals($update['_id'], 1);

        $result = $index->getDocumentById(1);
        $this->assertNull($result);
    }

    public function testStatus()
    {
        $index = $this->getIndex();
        $this->addDocument($index);
        $status = $index->status();
        $this->assertEquals(1, $status['indexed_documents']);
        $keys = array_keys($status);

        $this->assertArrayHasKey('disk_bytes', $status);
    }


    public function testDescribe()
    {
        $index = $this->getIndex();
        $keys = array_keys($index->describe());
        sort($keys);
        $this->assertEquals([
            'gid',
            'id',
            'label',
            'props',
            'tags',
            'title',
        ], $keys);
    }

    public function testAlterDrop()
    {
        $index = $this->getIndex();
        $response = $index->alter('drop', 'props');
        $this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);

        // use describe to demonstrate the field has been removed
        $keys = array_keys($index->describe());
        sort($keys);
        $this->assertEquals([
            'gid',
            'id',
            'label',
            'tags',
            'title',
        ], $keys);
    }

    public function testAlterAdd()
    {
        $index = $this->getIndex();
        $response = $index->alter('add', 'example', 'string');
        $this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);

        // use describe to demonstrate the field has been removed
        $description = $index->describe();
        $keys = array_keys($description);
        sort($keys);
        $this->assertEquals([
            'example',
            'gid',
            'id',
            'label',
            'props',
            'tags',
            'title',
        ], $keys);

        $this->assertEquals(['Type' => 'string', 'Properties' => ''], $description['example']);
    }

    public function testAlterInvalidOperation()
    {
        $index = $this->getIndex();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Alter operation not recognized');
        $response = $index->alter('invalidOperation', 'example', 'string');
    }

    public function testTruncate()
    {
        $index = $this->getIndex();
        $response = $index->truncate();
        $this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);
    }

    public function testOptimze()
    {
        $index = $this->getIndex();
        $response = $index->optimize(true);
        $this->assertEquals(['total' => 0, 'error' => '', 'warning' => ''], $response);
    }

    public function testFlush()
    {
        $index = $this->getIndex();
        $response = $index->flush();

        // @todo Is this correct?
        $this->assertEquals(null, $response);
    }

    public function testFlushRamChunk()
    {
        $index = $this->getIndex();
        $response = $index->flushramchunk();

        // @todo Is this correct?
        $this->assertEquals(null, $response);
    }


    public function testSearch()
    {
        $index = $this->getIndex();
        $this->addDocument($index);
        $result = $index->search('testing')->get();
        $this->assertCount(1, $result);
        $index->drop();
    }

    public function testIndexSuggest()
    {
        $index = $this->getIndex(true);
        $this->addDocument($index);
        $result = $index->suggest('tasting', []);
        $this->assertEquals(['distance' => 1, 'docs' => 1], $result['testing']);
    }

    public function testIndexExplainQuery()
    {
        $index = $this->getIndex(true);
        $result = $index->explainQuery('test');
        $this->assertEquals('AND(KEYWORD(test, querypos=1))', $result['transformed_tree']);
    }

    public function testIndexKeywords()
    {
        $index = $this->getIndex(true);
        $this->addDocument($index);
        $result = $index->keywords('tasting', []);

        // @todo Is this correct functionality
        $this->assertEquals(['tokenized' => 'tasting', 'normalized' => 'tasting', 'qpos' => '1'], $result[0]);
    }

    public function testStart()
    {
        $index = $this->getIndex();

        $index->setName('test');
        $index->drop(true);
        $index->create(
            ['title' => [
                'type' => 'text'],
                'plot' => ['type' => 'text'],
                'year' => ['type' => 'integer'],
                'rating' => ['type' => 'float']
            ],
            [],
            true
        );
        $index->addDocument(
            [
                'title' => 'Star Trek: Nemesis',
                'plot' => 'The Enterprise is diverted to the Romulan homeworld Romulus, supposedly because they want' .
                    ' to negotiate a peace treaty. Captain Picard and his crew discover a serious threat to the ' .
                    'Federation once Praetor Shinzon plans to attack Earth.',
                'year' => 2002,
                'rating' => 6.4
            ],
            1
        );

        $index->addDocuments([
            ['id' => 2, 'title' => 'Interstellar', 'plot' => 'A team of explorers travel through a wormhole in space' .
                ' in an attempt to ensure humanity\'s survival.', 'year' => 2014, 'rating' => 8.5],
            ['id' => 3, 'title' => 'Inception', 'plot' => 'A thief who steals corporate secrets through the use of' .
                ' dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.',
                'year' => 2010, 'rating' => 8.8],
            ['id' => 4, 'title' => '1917 ', 'plot' => ' As a regiment assembles to wage war deep in enemy territory,' .
                ' two soldiers are assigned to race against time and deliver a message that will stop 1,600 men from' .
                ' walking straight into a deadly trap.', 'year' => 2018, 'rating' => 8.4],
            ['id' => 5, 'title' => 'Alien', 'plot' => ' After a space merchant vessel receives an unknown transmission'.
                ' as a distress call, one of the team\'s member is attacked by a mysterious life form and they soon' .
                ' realize that its life cycle has merely begun.', 'year' => 1979, 'rating' => 8.4]
        ]);

        $results = $index->search('space team')->get();

        foreach ($results as $hit) {
            $this->assertInstanceOf('Manticoresearch\ResultHit', $hit);
        }

        $results = $index->search('alien')
            ->filter('year', 'gte', 2000)
            ->filter('rating', 'gte', 8.0)
            ->sort('year', 'desc')
            ->highlight()
            ->get();

        foreach ($results as $hit) {
            $this->assertInstanceOf('Manticoresearch\ResultHit', $hit);
        }

        $response = $index->updateDocument(['year' => 2019], 4);
        $this->assertEquals(4, $response['_id']);

        $schema = $index->describe();
        $this->assertCount(5, $schema);

        $response = $index->updateDocuments(['year' => 2000], ['match' => ['*' => 'team']]);
        $this->assertEquals(2, $response['updated']);

        $response = $index->updateDocuments(['year' => 2000], new MatchQuery('team', '*'));
        $this->assertEquals(2, $response['updated']);

        $bool = new BoolQuery();
        $bool->must(new MatchQuery('team', '*'));
        $bool->must(new Range('rating', ['gte' => 8.5]));
        $response = $index->updateDocuments(['year' => 2000], $bool);
        $this->assertEquals(1, $response['updated']);

        $response = $index->deleteDocument(4);
        $this->assertEquals(4, $response['_id']);
        
        $response = $index->deleteDocumentsByIds([10]);
        $this->assertEquals('not found', $response['result']);
        
        $response = $index->deleteDocumentsByIds([5,6]);
        $this->assertEquals(5, $response['_id']);

        $response = $index->deleteDocuments(new Range('id', ['gte' => 100]));
        $this->assertEquals(0, $response['deleted']);


        $index->truncate();
        $results = $index->search('')
            ->get();
        $this->assertCount(0, $results);

        $newdoc ='{"title":"Tenet","plot":"Armed with only one word, Tenet, and fighting for the survival of the '.
            'entire world, a Protagonist journeys through a twilight world of international espionage on a mission '.
            'that will unfold in something beyond real time","year":2020,"rating":8.8}';
        $index->addDocument($newdoc);
        $index->addDocument(json_decode($newdoc));
        $results = $index->search('tenet')->get();
        $this->assertCount(2, $results);


        $response = $index->drop();
        $this->assertEquals('', $response['error']);
    }

    public function testPercolates()
    {
        $this->getIndex();
        $this->index->setName('pqtest');
        $this->index->drop(true);
        $this->index->create(
            ['title' => ['type' => 'text'], 'gid' => ['type' => 'integer']],
            ['type' => 'percolate'],
            true
        );
        $this->index->addDocument(['query' => 'find me'], 6);
        $this->index->addDocument(['query' => 'fast'], 8);
        $this->index->addDocument(['query' => 'something'], 7);
        $docs = [
            ['title' => 'find me fast'],
            ['title' => 'pick me'],
            ['title' => 'something else'],
            ['title' => 'this is false']
        ];
        $result = $this->index->percolate($docs);
        $this->assertEquals(3, $result->count());
        $result = $this->index->percolateToDocs($docs);
        $this->assertEquals(4, $result->count());
        $result->rewind();
        $doc = $result->current();
        $this->assertTrue($doc->hasQueries());
        $this->index->drop();
    }

    public function testGetClient()
    {
        $index = $this->getIndex();
        $this->assertInstanceOf('Manticoresearch\Client', $index->getClient());
    }


    public function testSetGetName()
    {
        $index = $this->getIndex();
        $this->assertEquals('testindex', $index->getName());
    }

    public function testRawSelectQuery()
    {
        $params = [
            'host' => $_SERVER['MS_HOST'],
            'port' => $_SERVER['MS_PORT'],
            'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT']
        ];
        $client = new Client($params);

        $index = $this->getIndex();

        $index->setName('test');
        $index->drop(true);
        $index->create(
            [
                'title' => ['type' => 'text'],
                'plot' => ['type' => 'text'],
                'year' => ['type' => 'integer'],
                'rating' => ['type' => 'float']
            ],
            [],
            true
        );
        $index->addDocument(
            [
                'title' => 'Star Trek: Nemesis',
                'plot' => 'The Enterprise is diverted to the Romulan homeworld Romulus, supposedly because they want' .
                    ' to negotiate a peace treaty. Captain Picard and his crew discover a serious threat to the ' .
                    'Federation once Praetor Shinzon plans to attack Earth.',
                'year' => 2002,
                'rating' => 6.4
            ],
            1
        );
        $result = $client->sql([
            'mode' => 'raw',
            'body' => [
                'query' => 'select id, year from test where year = 2000'
            ]
        ]);
        $this->assertEquals([], $result);

        $result = $client->sql('select id, year from test where year = 2000', true);
        $this->assertEquals([], $result);
    }
}
