<?php
namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class KeywordsTest  extends \PHPUnit\Framework\TestCase
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
                'query'=>'product',
                'options' => [
                    'stats' =>1,
                    'fold_lemmas' => 1
                ]
            ]
        ];
        $response = static::$client->keywords($params);
        $this->assertSame('product',$response['1']['normalized']);
    }
}
