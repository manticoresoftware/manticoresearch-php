<?php


use Manticoresearch\Client;
use Manticoresearch\Exceptions\ConnectionException;

class SearchTest  extends \PHPUnit\Framework\TestCase
{
    public function testEmptyBody()
    {
        $params = ['host' => 'manticoresearch-manticore', 'port' => 9308];
        $client = new Client($params);
        $this->expectException(\Manticoresearch\Exceptions\ResponseException::class);
        $client->search(['body'=>'']);
    }

    public function testNoArrayParams()
    {
        $params = ['host' => 'manticoresearch-manticore', 'port' => 9308];
        $client = new Client($params);
        $this->expectException(TypeError::class);
        $client->search('this is not a json');
    }
    public function testMissingIndex()
    {
        $params = ['host' => 'manticoresearch-manticore', 'port' => 9308];
        $client = new Client($params);
        $this->expectException(\Manticoresearch\Exceptions\ResponseException::class);
        $response =$client->search( [
            'body' => [

                'query' => [
                    'match_phrase' => [
                        'movie_title' => 'star trek nemesis',
                    ]
                ]
            ]
        ]);

    }

    public function testPath()
    {
        $search = new \Manticoresearch\Endpoints\Search();
        $this->assertEquals('/json/search', $search->getPath());
    }

}