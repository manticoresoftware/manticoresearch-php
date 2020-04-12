<?php
namespace Manticoresearch\Test\Endpoints;


use Manticoresearch\Client;
use Manticoresearch\Endpoints\Update;
use Manticoresearch\Exceptions\RuntimeException;
use Manticoresearch\Test\Helper\PopulateHelperTest;

class UpdateTest  extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    private static $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $helper = new PopulateHelperTest();
        error_log('Setting up manticore index');
        $helper->populateForKeywords();
        self::$client = $helper->getClient();

        error_log(print_r($helper->describe('products'), 1));
    }

    public function testGetPath()
    {
        $update = new Update();
        $this->assertEquals('/json/update', $update->getPath());
    }

    public function testGetMethod()
    {
        $update = new Update();
        $this->assertEquals('POST', $update->getMethod());
    }

    /**
     * @todo GBA: This breaks with Manticore complaining that the attribute title cannot be found.  Why?
     */
    public function testUpdateProduct()
    {
        error_log('Testing update product');

        $partial = [
            'body' => [
                'index' => 'products',
                'id' => 100,
                'doc' => [
                    'title' =>'this product is not broken.  Hooray!',
                    'price' => 4.99
                ]
            ]
        ];
        $result = self::$client->update($partial);
        print_r($result);
    }
}
