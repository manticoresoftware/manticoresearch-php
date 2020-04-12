<?php
namespace Manticoresearch\Test\Endpoints;

use Manticoresearch\Client;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class BulkTest  extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    private static $client;

    /** @var PopulateHelperTest */
    private static $helper;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $helper = new PopulateHelperTest();
        $helper->populateForKeywords();
        self::$client = $helper->getClient();
        self::$helper = $helper;
    }
    public function testBulk()
    {
        $bulk =[
            ['insert'=> ['index'=>'products','id'=>100,'doc'=>['title'=>'Blue Toy Car','price' => '10.00']]],
            ['insert'=> ['index'=>'products','id'=>101,'doc'=>['title'=>'Red Toy Car','price' => '5.00']]],
            ['insert'=> ['index'=>'products','id'=>102,'doc'=>['title'=>'Green Toy Car','price' => '10.00']]],
            ['insert'=> ['index'=>'products','id'=>103,'doc'=>['title'=>'Purple Toy Car','price' => '20.00']]],
        ];
        self::$client->bulk(['body'=>$bulk]);

        // expect one document returned for each color
        self::$helper->search('products', 'Blue', 1);
        self::$helper->search('products', 'red', 1);
        self::$helper->search('products', 'green', 1);
        self::$helper->search('products', 'purple', 1);

        $description = self::$helper->describe('products');
        $this->assertEquals([], $description);
    }
}
