<?php


namespace Manticoresearch\Test;


use Manticoresearch\Client;
use Manticoresearch\Index;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Match;
use Manticoresearch\Query\Range;
use PHPUnit\Framework\TestCase;


class IndexTest extends TestCase
{

    protected function _getIndex($keywords = false): Index
    {
        $params = ['host' => $_SERVER['MS_HOST'],'port'=> $_SERVER['MS_PORT']];
        $index = new Index(new Client($params));
        $index->setName('testindex');
        $index->drop(true);

        $options = [];
        if ($keywords === true) {
            $options = [

                    'dict' => 'keywords',
                    'min_infix_len' => 2

            ];
        }
        $index->create([
            'title' => ['type' => 'text'],
            'gid' => ['type' => 'int'],
            'label' => ['type' => 'string'],
            'tags' => ['type' => 'multi'],
            'props' => ['type' => 'json']
        ], $options);
        return $index;
    }

    protected function _addDocument($index)
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

    public function testClassOfHit()
    {
        $index = $this->_getIndex();
        $this->_addDocument($index);
        $hit = $index->getDocumentById(1);
        $this->assertInstanceOf('Manticoresearch\ResultHit', $hit);
    }

    public function testClassOfNonExistentHit()
    {
        $index = $this->_getIndex();
        $this->_addDocument($index);
        $hit = $index->getDocumentById(2);
        $this->assertNull($hit);
    }

    public function testUpdateTagsThenDeleteDocument()
    {
        $index = $this->_getIndex();
        $this->_addDocument($index);
        $update = $index->updateDocument(['tags' => [10, 12, 14]], 1);
        $this->assertEquals($update['_id'], 1);

        $index->deleteDocument(1);
        $this->assertEquals($update['_id'], 1);

        $result = $index->getDocumentById(1);
        $this->assertNull($result);
    }

    public function testStatus()
    {
        $index = $this->_getIndex();
        $this->_addDocument($index);
        $status = $index->status();
        $this->assertEquals(1, $status['indexed_documents']);
        $keys = array_keys($status);
        sort($keys);

        $this->assertEquals([
            'disk_bytes',
            'disk_chunks',
            'found_rows_15min',
            'found_rows_1min',
            'found_rows_5min',
            'found_rows_total',
            'index_type',
            'indexed_bytes',
            'indexed_documents',
            'mem_limit',
            'query_time_15min',
            'query_time_1min',
            'query_time_5min',
            'query_time_total',
            'ram_bytes',
            'ram_bytes_retired',
            'ram_chunk',
            'ram_chunks_count',
            'tid',
            'tid_saved',
        ], $keys);
    }


    public function testDescribe()
    {
        $index = $this->_getIndex();
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

    public function testTruncate()
    {
        $index = $this->_getIndex();
        $response = $index->truncate();
        $this->assertEquals( ['total'=>0,'error'=>'','warning'=>''],$response);
    }

    public function testOptimze()
    {
        $index = $this->_getIndex();
        $response = $index->optimize(true);
        $this->assertEquals( ['total'=>0,'error'=>'','warning'=>''],$response);
    }

    public function testFlush()
    {
        $index = $this->_getIndex();
        $response = $index->flush();

        // @todo Is this correct?
        $this->assertEquals( null,$response);
    }

    public function testFlushRamChunk()
    {
        $index = $this->_getIndex();
        $response = $index->flushramchunk();

        // @todo Is this correct?
        $this->assertEquals( null,$response);
    }


    public function testSearch()
    {
        $index = $this->_getIndex();
        $this->_addDocument($index);
        $result = $index->search('testing')->get();
        $this->assertCount(1, $result);
        $index->drop();
    }

    public function testIndexSuggest()
    {
        $index = $this->_getIndex(true);
        $this->_addDocument($index);
        $result = $index->suggest('tasting', []);
        $this->assertEquals(['distance' => 1, 'docs' => 1], $result['testing']);
    }

    public function testStart()
    {
        $index = $this->_getIndex();

        $index->setName('test');
        $index->drop(true);
        $index->create(['title' => ['type' => 'text'], 'plot' => ['type' => 'text'], 'year' => ['type' => 'integer'], 'rating' => ['type' => 'float']]);
        $index->addDocument([
            'title' => 'Star Trek: Nemesis',
            'plot' => 'The Enterprise is diverted to the Romulan homeworld Romulus, supposedly because they want to negotiate a peace treaty. Captain Picard and his crew discover a serious threat to the Federation once Praetor Shinzon plans to attack Earth.',
            'year' => 2002,
            'rating' => 6.4
        ],
            1);
       $index->addDocuments([
            ['id'=>2,'title'=>'Interstellar','plot'=>'A team of explorers travel through a wormhole in space in an attempt to ensure humanity\'s survival.','year'=>2014,'rating'=>8.5],
            ['id'=>3,'title'=>'Inception','plot'=>'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.','year'=>2010,'rating'=>8.8],
            ['id'=>4,'title'=>'1917 ','plot'=>' As a regiment assembles to wage war deep in enemy territory, two soldiers are assigned to race against time and deliver a message that will stop 1,600 men from walking straight into a deadly trap.','year'=>2018,'rating'=>8.4],
            ['id'=>5,'title'=>'Alien','plot'=>' After a space merchant vessel receives an unknown transmission as a distress call, one of the team\'s member is attacked by a mysterious life form and they soon realize that its life cycle has merely begun.','year'=>1979,'rating'=>8.4]
        ]);

        $results = $index->search('space team')->get();

        foreach($results as $hit) {
            $this->assertInstanceOf('Manticoresearch\ResultHit', $hit);
        }

        $results = $index->search('alien')
            ->filter('year','gte',2000)
            ->filter('rating','gte',8.0)
            ->sort('year','desc')
            ->highlight()
            ->get();

        foreach($results as $hit) {
            $this->assertInstanceOf('Manticoresearch\ResultHit', $hit);
        }

        $response = $index->updateDocument(['year'=>2019],4);
        $this->assertEquals(4, $response['_id']);

        $schema = $index->describe();
        $this->assertCount(5, $schema);

        $response = $index->updateDocuments(['year'=>2000], ['match'=>['*'=>'team']]);
        $this->assertEquals(2, $response['updated']);

        $response = $index->updateDocuments(['year'=>2000], new Match('team','*'));
        $this->assertEquals(2, $response['updated']);

        $bool = new BoolQuery();
        $bool->must(new Match('team','*'));
        $bool->must(new Range('rating',['gte'=>8.5]));
        $response = $index->updateDocuments(['year'=>2000], $bool);
        $this->assertEquals(1, $response['updated']);

        $response = $index->deleteDocument(4);
        $this->assertEquals(4, $response['_id']);

        $response = $index->deleteDocuments(new Range('id',['gte'=>100]));
        $this->assertEquals(0, $response['deleted']);


        $index->truncate();
        $results = $index->search('')
            ->get();
        $this->assertCount(0, $results);
        $response = $index->drop();
        $this->assertEquals('', $response['error']);
    }


    public function testGetClient()
    {
        $index = $this->_getIndex();
        $this->assertInstanceOf('Manticoresearch\Client', $index->getClient());
    }


    public function testSetGetName()
    {
        $index = $this->_getIndex();
        $this->assertEquals('testindex', $index->getName());
    }
}
