<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class SuggestTest  extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    private static $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $helper = new PopulateHelperTest();
        $helper->populateForKeywords();
        self::$client = $helper->getClient();
    }
    public function testSuggest()
    {
        $params = [
            'index' => 'products',
            'body' => [
                'query'=>'brokn',
                'options' => [
                    'limit' =>5
                ]
            ]
        ];
        $response = self::$client->suggest($params);
        $this->assertSame('broken',array_keys($response)[0]);

    }
    public function testSuggestBadIndex()
    {
        $params = [
            'index' => 'productsNOT',
            'body' => [
                'query'=>'brokn',
                'options' => [
                    'limit' =>5
                ]
            ]
        ];
        $this->expectException(\Manticoresearch\Exceptions\ResponseException::class);
        $this->expectExceptionMessage('no such index productsNOT');
        $response = static::$client->suggest($params);
    }
    public function testResponseExceptionViaSuggest()
    {
        $params = [
            'index' => 'productsNOT',
            'body' => [
                'query'=>'brokn',
                'options' => [
                    'limit' =>5
                ]
            ]
        ];

        try {
            $response = static::$client->suggest($params);
        } catch (ResponseException $ex) {
            $request = $ex->getRequest();
            $this->assertEquals("mode=raw&query=CALL SUGGEST('brokn','productsNOT',5 AS limit)", $request->getBody());

            $response = $ex->getResponse();
            $this->assertEquals('"no such index productsNOT"', $response->getError());
        }

    }
    public function testSuggestGetIndex()
    {
        $suggest = new \Manticoresearch\Endpoints\Suggest();
        $suggest->setIndex('products');
        $this->assertEquals('products', $suggest->getIndex());
    }
    public function testSuggestNoIndex()
    {
        $suggest = new \Manticoresearch\Endpoints\Suggest();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Index name is missing');
        $suggest->setBody([]);
    }
}
