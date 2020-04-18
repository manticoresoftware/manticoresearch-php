<?php


namespace Manticoresearch\Test;

use Manticoresearch\Client;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Distance;
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


    public function testDistanceArrayParamCreation()
    {
        $search = $this->_getSearch();
        $q = new BoolQuery();

        $q->must(new \Manticoresearch\Query\Distance([
            'location_anchor'=>
                ['lat'=>52.2, 'lon'=> 48.6],
            'location_source' =>
                ['lat', 'lon'],
            'location_distance' => '100 km'
        ]));

        $result = $search->search($q)->get();
        $this->assertCount(4, $result);
    }

    public function testDistanceArrayParamCreationNoLocationAnchor()
    {
        $search = $this->_getSearch();
        $q = new BoolQuery();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('source attributes not provided');
        $q->must(new \Manticoresearch\Query\Distance([
            'location_anchor'=>
                ['lat'=>52.2, 'lon'=> 48.6],
            'location_distance' => '100 km'
        ]));
    }

    public function testDistanceArrayParamCreationNoLocationDistancce()
    {
        $search = $this->_getSearch();
        $q = new BoolQuery();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('distance not provided');
        $q->must(new \Manticoresearch\Query\Distance([
            'location_anchor'=>
                ['lat'=>52.2, 'lon'=> 48.6],
            'location_source' =>
                ['lat', 'lon'],
        ]));
    }

    public function testDistanceArrayParamCreationNoLocationSource()
    {
        $search = $this->_getSearch();
        $q = new BoolQuery();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('anchors not provided');
        $q->must(new \Manticoresearch\Query\Distance([
            'location_source' =>
                ['lat', 'lon'],
            'location_distance' => '100 km'
        ]));
    }

    public function testDistanceUsingObject()
    {
        $search = $this->_getSearch();
        $q = new BoolQuery();
        $distanceQuery = new Distance();
        $distanceQuery->setAnchor(52.2, 48.6);
        $distanceQuery->setSource(['lat', 'lon']);
        $distanceQuery->setDistance('100 km');
        $distanceQuery->setDistanceType('adaptive'); // the default
        $q->must($distanceQuery);

        $result = $search->search($q)->get();

        $this->assertCount(4, $result);
    }

    public function testMatchExactPhrase()
    {
        $search = $this->_getSearch();
        $q = new BoolQuery();
        $q->must(new \Manticoresearch\Query\MatchPhrase('wormhole in space', 'title,plot'));
        $result = $search->search($q)->get();
        $this->assertCount(1, $result);

        $q->must(new \Manticoresearch\Query\MatchPhrase('WORMhoLE in space', 'title,plot'));
        $result = $search->search($q)->get();
        $this->assertCount(1, $result);
    }

    public function testMatchInexactPhrase()
    {
        $search = $this->_getSearch();
        $q = new BoolQuery();
        $q->must(new \Manticoresearch\Query\MatchPhrase('wormhole space', 'title,plot'));
        $result = $search->search($q)->get();
        $this->assertCount(0, $result);
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
        $q->should(new Match(['query' => 'team of explorers', 'operator' => 'and'], '*'));
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

    protected function _getResultSet()
    {
        $search = $this->_getSearch();
        $result = $search->search('"team of explorers"/2')->get();
        return $result;
    }

    protected function _getFirstResultHit()
    {
        $result = $this->_getResultSet();
        $result->rewind();
        $this->assertEquals(0, $result->key());
        return $result->current();
    }

    public function testResultSetNextRewind()
    {
        $result = $this->_getResultSet();
        $this->assertEquals(0, $result->key());

        $result->next();
        $this->assertEquals(1, $result->key());
        $result->next();
        $this->assertEquals(2, $result->key());
        $result->rewind();
        $this->assertEquals(0, $result->key());
    }

    public function testResultSetGetTotal()
    {
        $result = $this->_getResultSet();
        $this->assertEquals(4, $result->getTotal());
    }

    public function testResultSetGetTime()
    {
        $result = $this->_getResultSet();
        $this->assertGreaterThanOrEqual(0, $result->getTime());
    }

    public function testResultSetHasNotTimedOut()
    {
        $result = $this->_getResultSet();
        $this->assertFalse( $result->hasTimedout());
    }

    public function testResultSetGetResponse()
    {
        $result = $this->_getResultSet();
        $keys = array_keys($result->getResponse()->getResponse());
        sort($keys);
        $this->assertEquals(['hits', 'timed_out', 'took'], $keys );
    }

    public function testResultSetGetNullProfile()
    {
        $result = $this->_getResultSet();
        $this->assertNull($result->getProfile());
    }

        public function testResultHitGetScore()
    {
        $resultHit = $this->_getFirstResultHit();
        $this->assertEquals(3468, $resultHit->getScore());
    }

    public function testResultHitGetID()
    {
        $resultHit = $this->_getFirstResultHit();
        $this->assertEquals(6, $resultHit->getId());
    }

    public function testResultHitGetValue()
    {
        $resultHit = $this->_getFirstResultHit();
        $this->assertEquals(1986, $resultHit->get('year'));
        $this->assertEquals(1986, $resultHit->__get('year'));
    }

    public function testResultHitHasValue()
    {
        $resultHit = $this->_getFirstResultHit();
        $this->assertTrue($resultHit->has('year'));
        $this->assertTrue($resultHit->__isset('year'));
    }

    public function testResultHitDoesNotHaveValue()
    {
        $resultHit = $this->_getFirstResultHit();
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
        $resultHit = $this->_getFirstResultHit();
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
        $resultHit = $this->_getFirstResultHit();
        $arbitraryID = 668689;
        $resultHit->setId($arbitraryID);
        $this->assertEquals($arbitraryID, $resultHit->getId());
    }
}
