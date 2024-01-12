<?php


namespace Manticoresearch\Test;

use Manticoresearch\Client;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Distance;
use Manticoresearch\Query\Equals;
use Manticoresearch\Query\In;
use Manticoresearch\Query\MatchQuery;
use Manticoresearch\Query\Range;
use Manticoresearch\ResultSet;
use Manticoresearch\Search;
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
    /**
     * @var Search
     */
    private static $search;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$search = static::indexDocuments();
    }

    protected function setUp(): void
    {
        parent::setUp();
        static::$search->reset();
        static::$search->setIndex('movies');
    }

    protected static function indexDocuments(): Search
    {
        $params = [
            'host' => $_SERVER['MS_HOST'],
            'port' => $_SERVER['MS_PORT'],
            'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT']
        ];
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
                    'plot' => 'A team of explorers travel through a wormhole in space in an attempt to ensure'.
                        ' humanity\'s survival.',
                    'year' => 2014, 'rating' => 8.5,
                    'meta' => ['keywords' => ['astronaut', 'relativity', 'nasa'],
                        'genre' => ['drama', 'scifi', 'thriller']],
                    'lat' => 51.2, 'lon' => 47.5,
                    'advise' => 'PG-13'
                ]
            ]],
            ['insert' => ['index' => 'movies', 'id' => 3, 'doc' =>
                ['title' => 'Inception', 'plot' => 'A thief who steals corporate secrets through the use of'.
                    ' dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.',
                    'year' => 2010, 'rating' => 8.8,
                    'meta' => ['keywords' => ['dream', 'thief', 'subconscious'],
                        'genre' => ['action', 'scifi', 'thriller']],
                    'lat' => 51.9, 'lon' => 48.5,
                    'advise' => 'PG-13'
                ]
            ]],
            ['insert' => ['index' => 'movies', 'id' => 4, 'doc' =>
                ['title' => '1917 ', 'plot' => ' As a regiment assembles to wage war deep in enemy territory, two'.
                    ' soldiers are assigned to race against time and deliver a message that will stop 1,600 men from'.
                    ' walking straight into a deadly trap.',
                    'year' => 2018, 'rating' => 8.4,
                    'meta' => ['keywords' => ['death', ' trench'], 'genre' => ['drama', 'war']],
                    'lat' => 51.1, 'lon' => 48.1,
                    'advise' => 'PG-13'
                ]
            ]],
            ['insert' => ['index' => 'movies', 'id' => 5, 'doc' =>
                ['title' => 'Alien', 'plot' => ' After a space merchant vessel receives an unknown transmission as a'.
                    ' distress call, one of the team\'s member is attacked by a mysterious life form and they soon '.
                    'realize that its life cycle has merely begun.',
                    'year' => 1979, 'rating' => 8.4,
                    'meta' => ['keywords' => ['spaceship', 'monster', 'nasa'], 'genre' => ['scifi', 'horror']],
                    'lat' => 52.2, 'lon' => 48.9,
                    'advise' => 'R'
                ]
            ]],
            ['insert' => ['index' => 'movies', 'id' => 6, 'doc' =>
                ['title' => 'Aliens', 'plot' => ' Ellen Ripley is rescued by a deep salvage team of explorers after'.
                    ' being in hypersleep for 57 years. The moon that the Nostromo visited has been colonized by '.
                    'explorers, but contact is lost. This time, colonial marines have impressive firepower, but will'.
                    ' that be enough?',
                    'year' => 1986, 'rating' => 8.3,
                    'meta' => ['keywords' => ['alien', 'monster', 'soldier'],
                        'genre' => ['scifi', 'action', 'adventure']],
                    'lat' => 51.6, 'lon' => 48.0,
                    'advise' => 'R'
                ]
            ]],
            ['insert' => ['index' => 'movies', 'id' => 10, 'doc' =>
                ['title' => 'Alien 3', 'plot' => 'After her last encounter, without a team Ellen Ripley team of '.
                    'explorers crash-lands on Fiorina 161, a maximum security prison. When a series of strange and '.
                    'deadly events occur shortly after her arrival, Ripley realizes that she has brought along an '.
                    'unwelcome visitor.',
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

    /**
     * Helper method to return just the years from the results.  This is used to validate filtering and sorting
     * @param ResultSet $results
     * @param boolean $sort since Manticore 4 we don't implicitly sort by id, so the results can be sorted
     * randomly especially when there's no other implicit/explicit sorting (e.g. full-text ranking), so it makes
     * sense to sort explicitly
     */
    private function yearsFromResults($results, $sort = false)
    {
        $years = [];
        while ($results->valid()) {
            $hit = $results->current();
            $data = $hit->getData();
            $years[] = $data['year'];
            $results->next();
        }
        if ($sort != false) {
            sort($years);
        }
        return $years;
    }

    protected function getResultSet()
    {
        $result = static::$search->search('"team of explorers"/2')->get();
        return $result;
    }

    protected function getFirstResultHit()
    {
        $result = $this->getResultSet();
        $result->rewind();
        $this->assertEquals(0, $result->key());
        return $result->current();
    }

    public function testConstructor()
    {
        $params = [
            'host' => $_SERVER['MS_HOST'],
            'port' => $_SERVER['MS_PORT'],
            'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT']
        ];
        $client = new Client($params);
        $searchObj = new Search($client);
        $this->assertEquals($client, $searchObj->getClient());
    }

    public function testFilterLTE()
    {
        $results = static::$search->filter('year', 'lte', 1990)->get();
        $this->assertEquals([1979,1986], $this->yearsFromResults($results, 'sort'));
    }

    public function testFilterLTEAsObject()
    {
        $results = static::$search->filter(new Range('year', ['lte' => 1990]))->get();
        $this->assertEquals([1979,1986], $this->yearsFromResults($results, 'sort'));
    }

    public function testFilterGTE()
    {
        $results = static::$search->filter('year', 'gte', 1990)->sort('id', 'asc')->get();
        $this->assertEquals([1992,2010,2014,2018], $this->yearsFromResults($results, 'sort'));
    }

    public function testFilterEq()
    {

        $results = static::$search->filter('year', 'equals', 1979)->get();
        $this->assertCount(1, $results);
    }

    public function testFilterRange()
    {
        $results = static::$search->filter('year', 'range', [1960,1992])->get();
        $this->assertEquals([1979,1986,1992], $this->yearsFromResults($results, 'sort'));
    }

    public function testFilterIn()
    {
        $results = static::$search->filter('year', 'in', [1960,1979,1986])->get();
        $this->assertEquals([1979,1986], $this->yearsFromResults($results, 'sort'));
    }

    /**
     * Demonstrate that the array of years gets smaller for the same phrase match as the limit is applied
     */
    public function testLimitMethod()
    {
        $results = static::$search->limit(3)->phrase('team of explorers')->get();
        self::assertCount(3, $results);

        $results = static::$search->limit(2)->phrase('team of explorers')->get();
        self::assertCount(2, $results);

        $results = static::$search->limit(1)->phrase('team of explorers')->get();
        self::assertCount(1, $results);
    }

    /**
     * Demonstrate that the array of years gets smaller for the same phrase match as the limit is applied
     */
    public function testMaxMatchesMethod()
    {
        $results = static::$search->maxMatches(3)->phrase('team of explorers')->get();
        $this->assertEquals([1986,2014,1992], $this->yearsFromResults($results));

        $results = static::$search->maxMatches(2)->phrase('team of explorers')->get();
        $this->assertEquals([1986,2014], $this->yearsFromResults($results));

        $results = static::$search->maxMatches(1)->phrase('team of explorers')->get();
        $this->assertEquals([1986], $this->yearsFromResults($results));
    }

    public function testNotFilterLTE()
    {
        $results = static::$search->phrase('team of explorers')->notFilter('year', 'lte', 1990)->get();
        $this->assertEquals([2014,1992], $this->yearsFromResults($results));

        $results = static::$search->phrase('team of explorers')->notFilter('year', 'lte', 1992)->get();
        $this->assertEquals([2014], $this->yearsFromResults($results));
    }

    public function testNotFilterRange()
    {
        $results = static::$search->notFilter('year', 'range', [1900,1990])->get();
        $this->assertEquals([1992,2010,2014,2018], $this->yearsFromResults($results, 'sort'));
    }

    public function testNotFilterIn()
    {
        $results = static::$search->notFilter('year', 'in', [1960,1979,1986])->get();
        $this->assertEquals([1992,2010,2014,2018], $this->yearsFromResults($results, 'sort'));
    }

    public function testNotFilterRangeAsObject()
    {
        $range = new Range('year', ['gte' => 1900, 'lte' => 1990]);
        $results = static::$search->notFilter($range)->get();
        $this->assertEquals([1992,2010,2014,2018], $this->yearsFromResults($results, 'sort'));
    }

    public function testOrFilterRange()
    {
        $results = static::$search->phrase('team of explorers')->orFilter('year', 'range', [1900,1990])->get();
        $this->assertEquals([1986], $this->yearsFromResults($results));
    }

    public function testOrFilterRangeAsObject()
    {
        $range = new Range('year', ['gte' => 1900, 'lte' => 1990]);

        $results = static::$search->phrase('team of explorers')->orFilter($range)->get();
        $this->assertEquals([1986], $this->yearsFromResults($results));
    }

    /**
     * Search for years less than 1990, more than 1999
     */
    public function testOrFilterRangeSkip90s()
    {
        $results = static::$search->
            orFilter('year', 'lt', 1990)->
            orFilter('year', 'gte', 2000)->
            get();
        $this->assertEquals([1979,1986,2010,2014,2018], $this->yearsFromResults($results, 'sort'));
    }

    public function testOrFilterEquals()
    {
        $results = static::$search->
        orFilter('year', 'equals', 1979)->
        orFilter('year', 'equals', 1986)->
        get();
        $this->assertEquals([1979,1986], $this->yearsFromResults($results, 'sort'));
    }


    public function testSortMethodAscending()
    {
        $results = static::$search->sort('year')->phrase('team of explorers')->get();
        $this->assertEquals([1986,1992,2014], $this->yearsFromResults($results));
    }

    public function testSortMethodDescending()
    {
        $results = static::$search->sort('year', 'desc')->phrase('team of explorers')->get();
        $this->assertEquals([2014,1992,1986], $this->yearsFromResults($results));
    }

    public function testOffsetMethod()
    {
        $results = static::$search->offset(0)->phrase('team of explorers')->get();
        $this->assertEquals([1986,2014,1992], $this->yearsFromResults($results));

        $results = static::$search->offset(1)->phrase('team of explorers')->get();
        $this->assertEquals([2014,1992], $this->yearsFromResults($results));

        $results = static::$search->offset(2)->phrase('team of explorers')->get();
        $this->assertEquals([1992], $this->yearsFromResults($results));
    }

    public function testPhraseMethodAllFieldsMatchingPhrase()
    {
        $results = static::$search->phrase('team of explorers')->get();
        $this->assertCount(3, $results);
    }

    public function testPhraseMethodAllFieldsNoMatchingPhrase()
    {
        // search for a non matching phrase
        $results = static::$search->phrase('team with explorers')->get();
        $this->assertCount(0, $results);
    }

    public function testPhraseMethodSpecifiedFieldsTitleOnly()
    {
        // the title fields do not contain the matching text
        $results = static::$search->phrase('team of explorers', 'title')->get();
        $this->assertCount(0, $results);
    }

    public function testPhraseMethodSpecifiedFieldsPlotOnly()
    {
        $results = static::$search->phrase('team of explorers', 'plot')->get();
        $this->assertCount(3, $results);
    }

    public function testPhraseMethodSpecifiedFieldsTitleAndPlot()
    {
        $results = static::$search->phrase('team of explorers', 'title,plot')->get();
        $this->assertCount(3, $results);
    }

    public function testMatchExactPhrase()
    {
        $q = new BoolQuery();
        $q->must(new \Manticoresearch\Query\MatchPhrase('wormhole in space', 'title,plot'));
        $result = static::$search->search($q)->get();
        $this->assertCount(1, $result);

        $q->must(new \Manticoresearch\Query\MatchPhrase('WORMhoLE in space', 'title,plot'));
        $result = static::$search->search($q)->get();
        $this->assertCount(1, $result);
    }

    public function testMatchInexactPhrase()
    {
        $q = new BoolQuery();
        $q->must(new \Manticoresearch\Query\MatchPhrase('wormhole space', 'title,plot'));
        $result = static::$search->search($q)->get();
        $this->assertCount(0, $result);
    }


    public function testSearchDistanceMethod()
    {
        $result = static::$search->distance([
            'location_anchor'=>
                ['lat'=>52.2, 'lon'=> 48.6],
            'location_source' =>
                ['lat', 'lon'],
            'location_distance' => '100 km'
        ])->get();

        $this->assertCount(4, $result);
    }

    public function testDistanceObjectArrayParamCreation()
    {
        $q = new BoolQuery();

        $q->must(new Distance([
            'location_anchor'=>
                ['lat'=>52.2, 'lon'=> 48.6],
            'location_source' =>
                ['lat', 'lon'],
            'location_distance' => '100 km'
        ]));

        $result = static::$search->search($q)->get();
        $this->assertCount(4, $result);
    }

    public function testDistanceArrayParamCreation()
    {
        $q = new BoolQuery();

        $q->must(new Distance([
            'location_anchor'=>
                ['lat'=>52.2, 'lon'=> 48.6],
            'location_source' =>
                ['lat', 'lon'],
            'location_distance' => '100 km'
        ]));

        $result = static::$search->search($q)->get();
        $this->assertCount(4, $result);
    }

    public function testDistanceArrayParamCreationNoLocationAnchor()
    {
        $q = new BoolQuery();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('source attributes not provided');
        $q->must(new Distance([
            'location_anchor'=>
                ['lat'=>52.2, 'lon'=> 48.6],
            'location_distance' => '100 km'
        ]));
    }

    public function testDistanceArrayParamCreationNoLocationDistancce()
    {
        $q = new BoolQuery();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('distance not provided');
        $q->must(new Distance([
            'location_anchor'=>
                ['lat'=>52.2, 'lon'=> 48.6],
            'location_source' =>
                ['lat', 'lon'],
        ]));
    }

    public function testDistanceArrayParamCreationNoLocationSource()
    {
        $q = new BoolQuery();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('anchors not provided');
        $q->must(new Distance([
            'location_source' =>
                ['lat', 'lon'],
            'location_distance' => '100 km'
        ]));
    }

    public function testDistanceUsingObject()
    {
        $q = new BoolQuery();
        $distanceQuery = new Distance();
        $distanceQuery->setAnchor(52.2, 48.6);
        $distanceQuery->setSource(['lat', 'lon']);
        $distanceQuery->setDistance('100 km');
        $distanceQuery->setDistanceType('adaptive'); // the default
        $q->must($distanceQuery);

        $result = static::$search->search($q)->get();

        $this->assertCount(4, $result);
    }


    public function testTextSearch()
    {
        $result = static::$search->search('"team of explorers"/2')->get();
        $this->assertCount(4, $result);
    }

    public function testTextSearchFilterToAYear()
    {
        $result = static::$search->search('"team of explorers"/2')->filter('year', 'equals', 2014)->get();
        $this->assertCount(1, $result);
    }

    public function testMatchAllFieldsOrMatch()
    {
        $result = static::$search->match('team of explorers')->get();
        $this->assertCount(5, $result);
    }

    public function testMatchTitleOnly()
    {
        $result = static::$search->match(['query' => 'team of explorers', 'operator' => 'and'], 'title')->get();
        $this->assertCount(0, $result);
    }

    public function testMatchTitleAndPlot()
    {
        $result = static::$search->match(['query' => 'team of explorers', 'operator' => 'and'], 'title,plot')->get();
        $this->assertCount(3, $result);
    }

    public function testMatchAllFieldsAnd()
    {
        $result = static::$search->match(['query' => 'team of explorers', 'operator' => 'and'])->get();
        $this->assertCount(3, $result);
    }

    public function testMatchFilteredToSingleYear()
    {
        $result = static::$search->match(['query' => 'team of explorers', 'operator' => 'and'])->
            filter('year', 'equals', 2014)->get();
        $this->assertCount(1, $result);
    }

    public function testComplexSearchWithFilters()
    {
        $result = static::$search->search('"team of explorers"/2')
            ->expression('genre', "in(meta['genre'],'adventure')")
            ->notfilter('genre', 'equals', 1)
            ->filter('year', 'lte', 2000)
            ->filter("advise", 'equals', 'R')
            ->get();

        $this->assertCount(2, $result);
    }

    public function testMatchBoolQueryMust()
    {
        $q = new BoolQuery();
        $q->must(new MatchQuery(['query' => 'team of explorers', 'operator' => 'and'], '*'));
        $result = static::$search->search($q)->get();
        $this->assertCount(3, $result);
    }

    public function testMatchBoolQueryShould()
    {
        $q = new BoolQuery();
        $q->should(new MatchQuery(['query' => 'team of explorers', 'operator' => 'and'], '*'));
        $result = static::$search->search($q)->get();
        $this->assertCount(3, $result);
    }

    public function testBoolQueryMutipleFilters1()
    {
        $q = new BoolQuery();
        $q->must(new MatchQuery(['query' => 'team of explorers', 'operator' => 'or'], '*'));
        $q->must(new Equals('year', 2014));
        $result = static::$search->search($q)->get();
        $this->assertCount(1, $result);
    }

    public function testBoolQueryNestedFilters()
    {
        $q = new BoolQuery();
        $q->must(new Equals('advise', 'R'));
        $q2 = new BoolQuery();
        $q2->should(new Equals('rating', 8.4));
        $q2->should(new Equals('rating', 8.3));
        $q->must($q2);
        $result = static::$search->search($q)->get();
        $this->assertCount(2, $result);
    }

    public function testInFilter()
    {
        $q = new BoolQuery();
        $q->must(new MatchQuery(['query' => 'team of explorers', 'operator' => 'or'], '*'));
        $q->must(new In('year', [1992,2014]));
        $result = static::$search->search($q)->get();
        $this->assertCount(2, $result);
    }

    public function testBoolQueryMutipleFilters2()
    {
        $q = new BoolQuery();
        $q->must(new MatchQuery(['query' => 'team of explorers', 'operator' => 'or'], '*'));
        $q->must(new Range('year', ['lte' => 2020]));
        $result = static::$search->search($q)->get();
        $this->assertCount(5, $result);
    }

    public function testResultSetNextRewind()
    {
        $result = $this->getResultSet();
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
        $result = $this->getResultSet();
        $this->assertEquals(4, $result->getTotal());
    }

    public function testResultSetGetTime()
    {
        $result = $this->getResultSet();
        $this->assertGreaterThanOrEqual(0, $result->getTime());
    }

    public function testResultSetHasNotTimedOut()
    {
        $result = $this->getResultSet();
        $this->assertFalse($result->hasTimedout());
    }

    public function testResultSetGetResponse()
    {
        $result = $this->getResultSet();
        $keys = array_keys($result->getResponse()->getResponse());
        sort($keys);
        $this->assertEquals(['hits', 'timed_out', 'took'], $keys);
    }

    public function testResultSetGetNullProfile()
    {
        $result = $this->getResultSet();
        $this->assertNull($result->getProfile());
    }

    /**
     * @todo What is the intended functionality here?
     */
    public function testNonExistentSource()
    {
        $results = static::$search->setSource('source_does_not_exist')->phrase('team of explorers')->get();
        while ($results->valid()) {
            $hit = $results->current();
            $this->assertEquals([], $hit->getData());
            $results->next();
        }
    }

    public function testProfileForSearch()
    {
        $results = static::$search->profile()->phrase('team of explorers')->get();
        $profile = $results->getProfile();
        $expected = 'PHRASE( AND(KEYWORD(team, querypos=1)),  AND(KEYWORD(of, querypos=2)),  AND(KEYWORD(explorers, ' .
            'querypos=3)))';
        $this->assertEquals($expected, $profile['query']['description']);
    }

    public function testResultHitGetScore()
    {
        $resultHit = $this->getFirstResultHit();
        $this->assertEquals(3468, $resultHit->getScore());
    }

    public function testResultHitGetID()
    {
        $resultHit = $this->getFirstResultHit();
        $this->assertEquals(6, $resultHit->getId());
    }

    public function testResultHitGetValue()
    {
        $resultHit = $this->getFirstResultHit();
        $this->assertEquals(1986, $resultHit->get('year'));
        $this->assertEquals(1986, $resultHit->__get('year'));
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

    public function testGetHighlight()
    {
        $results = static::$search->match('salvage')->highlight(
            ['plot'],
            ['pre_tags' => '<i>','post_tags'=>'</i>']
        )->get();

        $this->assertEquals(1, $results->count());
        $this->assertEquals(
            ['plot' => [' is rescued by a deep <i>salvage</i> team of explorers after being']],
            $results->current()->getHighlight()
        );
    }

    public function testHighlightParamsMissing()
    {
        $results = static::$search->match('salvage')->highlight()->get();

        $this->assertEquals(1, $results->count());

        // default highlighter is bold, all text fields are searched.  The 'plot field' has a highlights match
        $this->assertCount(2, $results->current()->getHighlight());
    }

    public function testBodyHasOptions()
    {
        $body = static::$search
            ->option('retry_count', 3)->option('field_weights', ['title' => 2, 'plot' => 1])
            ->compile();

        $this->assertEquals(['retry_count' => 3, 'field_weights' => ['title' => 2, 'plot' => 1]], $body['options']);
    }

    public function testUnsetOption()
    {
        static::$search->option('retry_count', 3)->option('retry_delay', 4);
        static::$search->option('retry_count', null);
        $body = static::$search->compile();

        $this->assertEquals(['retry_delay' => 4], $body['options']);
    }

    public function testCutoffOption()
    {
        $this->assertCount(2, static::$search->search('')->option('cutoff', 2)->get());
    }

    public function testTrackScoresCompiles()
    {
        $body = static::$search->trackScores(true)->compile();
        $this->assertTrue($body['track_scores']);

        $body = static::$search->trackScores(false)->compile();
        $this->assertFalse($body['track_scores']);

        $body = static::$search->trackScores(null)->compile();
        $this->assertArrayNotHasKey('track_scores', $body);
    }

    public function testTrackScores()
    {
        // when there are match and sort, but there is no track_scores, the score is equal to 1
        $result = static::$search->search('space')->sort('year', 'desc')->get();
        $this->assertCount(2, $result);
        foreach ($result as $resultHit) {
            $this->assertEquals(1, $resultHit->getScore());
        }

        // when there are match, sort and track_scores, the score is greater than 1
        $result = static::$search->search('space')->trackScores(true)->sort('year', 'desc')->get();
        $this->assertCount(2, $result);
        foreach ($result as $resultHit) {
            $this->assertGreaterThan(1, $resultHit->getScore());
        }
    }

    public function testStripBadUtf8Compiles()
    {
        $body = static::$search->stripBadUtf8(true)->compile();
        $this->assertTrue($body['strip_bad_utf8']);
        
        $body = static::$search->stripBadUtf8(false)->compile();
        $this->assertFalse($body['strip_bad_utf8']);
        
        $body = static::$search->stripBadUtf8(null)->compile();
        $this->assertArrayNotHasKey('strip_bad_utf8', $body);
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

    public function testGetBody()
    {

        static::$search->phrase('team of explorers')->get();
        $body = static::$search->getBody();
        $this->assertEquals([
            'index' => 'movies',
            'query' =>
                [
                    'bool' =>
                        [
                            'must' =>
                                [
                                    0 =>
                                        [
                                            'match_phrase' =>
                                                [
                                                    '*' => 'team of explorers',
                                                ],
                                        ],
                                ],
                        ],
                ],
        ], $body);
    }


    public function testGetClient()
    {
        $client = static::$search->getClient();
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testFacets()
    {
        $results = static::$search->filter('year', 'range', [1960,1992])->facet('year')->get();
        $facets = $results->getFacets();
        $this->assertCount(1, $facets);
        $this->assertArrayHasKey('year', $facets);
        $this->assertCount(3, $facets['year']['buckets']);
    }
}
