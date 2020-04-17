<?php


namespace Manticoresearch\Test;

use Manticoresearch\Client;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Equals;
use Manticoresearch\Query\Match;
use Manticoresearch\Query\Range;
use Manticoresearch\Search;
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{

    protected function _getSearch(): Search
    {
        $params = ['host' => $_SERVER['MS_HOST'], 'port' => $_SERVER['MS_PORT']];

        $client = new Client($params);
        $client->indices()->drop(['index' => 'movies','body'=>['silent'=>true]]);
        $index = [
            'index' => 'movies',
            'body' => [
                'columns' => ['title' => ['type' => 'text'],
                    'plot' => ['type' => 'text'],
                    'year' => ['type' => 'integer'],
                    'rating' => ['type' => 'float'],
                    'language' => ['type' => 'multi'],
                    'meta' => ['type' => 'json'],
                    'lat' => ['type' => 'float'],
                    'lon' => ['type' => 'float'],
                    'advise' => ['type' => 'string']
                ]
            ]
        ];

        $client->indices()->create($index);
        $docs = [
            ['insert' => ['index' => 'movies', 'id' => 2, 'doc' =>
                ['title' => 'Interstellar',
                    'plot' => 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity\'s survival.',
                    'year' => 2014, 'rating' => 8.5,
                    'meta' => ['keywords' => ['astronaut', 'relativity', 'nasa'], 'genre' => ['drama', 'scifi', 'thriller']],
                    'lat' => 51.2, 'lon' => 47.5,
                    'advise' => 'PG-13'
                ]
            ]],
            ['insert' => ['index' => 'movies', 'id' => 3, 'doc' =>
                ['title' => 'Inception', 'plot' => 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.',
                    'year' => 2010, 'rating' => 8.8,
                    'meta' => ['keywords' => ['dream', 'thief', 'subconscious'], 'genre' => ['action', 'scifi', 'thriller']],
                    'lat' => 51.9, 'lon' => 48.5,
                    'advise' => 'PG-13'
                ]
            ]],
            ['insert' => ['index' => 'movies', 'id' => 4, 'doc' =>
                ['title' => '1917 ', 'plot' => ' As a regiment assembles to wage war deep in enemy territory, two soldiers are assigned to race against time and deliver a message that will stop 1,600 men from walking straight into a deadly trap.',
                    'year' => 2018, 'rating' => 8.4,
                    'meta' => ['keywords' => ['death', ' trench'], 'genre' => ['drama', 'war']],
                    'lat' => 51.1, 'lon' => 48.1,
                    'advise' => 'PG-13'
                ]
            ]],
            ['insert' => ['index' => 'movies', 'id' => 5, 'doc' =>
                ['title' => 'Alien', 'plot' => ' After a space merchant vessel receives an unknown transmission as a distress call, one of the team\'s member is attacked by a mysterious life form and they soon realize that its life cycle has merely begun.',
                    'year' => 1979, 'rating' => 8.4,
                    'meta' => ['keywords' => ['spaceship', 'monster', 'nasa'], 'genre' => ['scifi', 'horror']],
                    'lat' => 52.2, 'lon' => 48.9,
                    'advise' => 'R'
                ]
            ]],
            ['insert' => ['index' => 'movies', 'id' => 6, 'doc' =>
                ['title' => 'Aliens', 'plot' => ' Ellen Ripley is rescued by a deep salvage team of explorers after being in hypersleep for 57 years. The moon that the Nostromo visited has been colonized by explorers, but contact is lost. This time, colonial marines have impressive firepower, but will that be enough?',
                    'year' => 1986, 'rating' => 8.3,
                    'meta' => ['keywords' => ['alien', 'monster', 'soldier'], 'genre' => ['scifi', 'action', 'adventure']],
                    'lat' => 51.6, 'lon' => 48.0,
                    'advise' => 'R'
                ]
            ]],
            ['insert' => ['index' => 'movies', 'id' => 10, 'doc' =>
                ['title' => 'Alien 3', 'plot' => 'After her last encounter, without a team Ellen Ripley team of explorers crash-lands on Fiorina 161, a maximum security prison. When a series of strange and deadly events occur shortly after her arrival, Ripley realizes that she has brought along an unwelcome visitor.',
                    'year' => 1992, 'rating' => 6.5,
                    'meta' => ['keywords' => ['alien', 'prison', 'android'], 'genre' => ['scifi', 'horror', 'action']],
                    'lat' => 51.8, 'lon' => 48.2,
                    'advise' => 'R'
                ]
            ]]
        ];
        $client->bulk(['body' => $docs]);

        $search = new Search($client);
        $search->setIndex('movies');
        return $search;
    }

    public function testSearch()
    {
        $search = $this->_getSearch();
        $result = $search->search('"team of explorers"/2')->get();
        $this->assertCount(4, $result);
        $search->reset();
        $search->setIndex('movies');

        $result = $search->search('"team of explorers"/2')->filter('year', 'equals', 2014)->get();
        $this->assertCount(1, $result);
        $search->reset();
        $search->setIndex('movies');

        $result = $search->match('team of explorers')->get();
        $this->assertCount(5, $result);
        $search->reset();
        $search->setIndex('movies');


        $result = $search->match(['query' => 'team of explorers', 'operator' => 'and'])->get();
        $this->assertCount(3, $result);
        $search->reset();
        $search->setIndex('movies');

        $result = $search->match(['query' => 'team of explorers', 'operator' => 'and'])->filter('year', 'equals', 2014)->get();
        $this->assertCount(1, $result);
        $search->reset();
        $search->setIndex('movies');


        $search = $this->_getSearch();
        $result = $search->search('"team of explorers"/2')
            ->expression('genre', "in(meta['genre'],'adventure')")
            ->notfilter('genre', 'equals', 1)
            ->filter('year', 'lte', 2000)
            ->filter("advise", 'equals', 'R')
            ->get();

        $this->assertCount(2, $result);
        $search->reset();
        $search->setIndex('movies');

        $q = new BoolQuery();
        $q->must(new Match(['query' => 'team of explorers', 'operator' => 'and'], '*'));
        $result = $search->search($q)->get();
        $this->assertCount(3, $result);
        $search->reset();
        $search->setIndex('movies');


        $q = new BoolQuery();
        $q->must(new Match(['query' => 'team of explorers', 'operator' => 'or'], '*'));
        $q->must(new Equals('year', 2014));
        $result = $search->search($q)->get();
        $this->assertCount(1, $result);
        $search->reset();
        $search->setIndex('movies');

        $q = new BoolQuery();
        $q->must(new Match(['query' => 'team of explorers', 'operator' => 'or'], '*'));
        $q->must(new Range('year', ['lte' => 2020]));
        $result = $search->search($q)->get();
        $this->assertCount(5, $result);
        $search->reset();
        $search->setIndex('movies');
    }

    protected function getFirstResultHit()
    {
        $search = $this->_getSearch();
        $result = $search->search('"team of explorers"/2')->get();
        $result->rewind();
        $result->next();
        return $result->current();
    }

    public function testResultHitGetScore()
    {
        $resultHit = $this->getFirstResultHit();
        $this->assertEquals(3464, $resultHit->getScore());
    }

    public function testResultHitGetID()
    {
        $resultHit = $this->getFirstResultHit();
        $this->assertEquals(2, $resultHit->getId());
    }

    public function testResultHitGetValue()
    {
        $resultHit = $this->getFirstResultHit();
        $this->assertEquals(2014, $resultHit->get('year'));
        $this->assertEquals(2014, $resultHit->__get('year'));
    }

    public function testResultHitHasValue()
    {
        $resultHit = $this->getFirstResultHit();
        $this->assertTrue($resultHit->has('year'));
        $this->assertTrue($resultHit->__isset('year'));
    }

    public function testResultHitDoesNotHaveValue()
    {
        $resultHit = $this->getFirstResultHit();
        $this->assertFalse($resultHit->has('nonExistentKey'));
        $this->assertFalse($resultHit->__isset('nonExistentKey'));
        $this->assertEquals([], $resultHit->get('nonExistentKey'));
    }

    public function testResultHitDoesGetHighlight()
    {
        $this->markTestSkipped('TODO - highlight check');
    }

    public function testResultHitGetData()
    {
        $resultHit = $this->getFirstResultHit();
        $keys = array_keys($resultHit->getData());
        sort($keys);
        $this->assertEquals([
            0 => 'advise',
            1 => 'language',
            2 => 'lat',
            3 => 'lon',
            4 => 'meta',
            5 => 'plot',
            6 => 'rating',
            7 => 'title',
            8 => 'year',
        ], $keys);
    }


    public function testSetGetID()
    {
        $resultHit = $this->getFirstResultHit();
        $arbitraryID = 668689;
        $resultHit->setId($arbitraryID);
        $this->assertEquals($arbitraryID, $resultHit->getId());
    }
}
