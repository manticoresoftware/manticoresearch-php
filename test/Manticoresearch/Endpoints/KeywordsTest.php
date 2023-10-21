<?php

namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;
use Manticoresearch\Endpoints\Keywords;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class KeywordsTest extends \PHPUnit\Framework\TestCase
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

    public function testKeywords()
    {
        $params = [
            'index' => 'products',
            'body' => [
                'query' => 'product',
                'options' => [
                    'stats' => 1,
                    'fold_lemmas' => 1
                ]
            ]
        ];
        $response = static::$client->keywords($params);
        $this->assertSame('product', $response['0']['normalized']);
    }

    public function testKeywordsBadIndex()
    {
        $params = [
            'index' => 'productsNOT',
            'body' => [
                'query' => 'product',
                'options' => [
                    'stats' => 1,
                    'fold_lemmas' => 1
                ]
            ]
        ];

        // Adding extra try-catch to provide compatibility with previous Manticore versions
        try {
            $response = static::$client->keywords($params);
        } catch (ResponseException $e) {
            try {
                $this->assertEquals('"no such index productsNOT"', $e->getMessage());
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->expectException(ResponseException::class);
                $this->expectExceptionMessage('no such table productsNOT');
                $response = static::$client->keywords($params);
            }
        }
    }

    public function testSetGetIndex()
    {
        $kw = new Keywords();
        $kw->setIndex('products');
        $this->assertEquals('products', $kw->getIndex());
    }
}
