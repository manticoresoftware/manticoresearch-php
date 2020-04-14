<?php


namespace Manticoresearch\Test;


use Manticoresearch\Index;
use Manticoresearch\Client;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{

    protected function _getIndex(): Index
    {

        $params = ['host' => $_SERVER['MS_HOST'], $_SERVER['MS_PORT']];
        $index = new Index(new Client($params));
        $index->setName('testindex');
        $index->drop(true);
        $index->create(['title' => ['type' => 'text'], 'gid' => ['type' => 'int'], 'label' => ['type' => 'string'], 'tags' => ['type' => 'multi'], 'props' => ['type' => 'json']], []);
        return $index;
    }

    public function testDocuments()
    {
        $index = $this->_getIndex();
        $index->addDocument([
            'title' => 'find me',
            'gid' => 1,
            'label' => 'not used',
            'tags' => [1, 2, 3],
            'props' => [
                'color' => 'blue',
                'rule' => ['one', 'two']
            ]
        ], 1);
        $hit = $index->getDocumentById(1);
        $this->assertInstanceOf('Manticoresearch\ResultHit',$hit);
        $hit = $index->getDocumentById(2);
        $this->assertNull($hit);

        $update = $index->updateDocument(['tags' => [10, 12, 14]], 1);
        $this->assertEquals($update['_id'], 1);
        $delete = $index->deleteDocument(1);
        $this->assertEquals($update['_id'], 1);
        $result = $index->getDocumentById(1);
        $this->assertEquals($result['hits']['total'], 0);
        $index->drop();

    }

    public function testSearch()
    {
        $index = $this->_getIndex();
        $index->addDocument([
            'title' => 'find me',
            'gid' => 1,
            'label' => 'not used',
            'tags' => [1, 2, 3],
            'props' => [
                'color' => 'blue',
                'rule' => ['one', 'two']
            ]
        ], 1);
        $result = $index->search('find')->get();
        $this->assertCount(1,$result);
        $index->drop();
    }
}