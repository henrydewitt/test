<?php
namespace tests;

include(__DIR__ . "\\..\\autoload.php");

use GuzzleHttp\Client;
use PHPUnit_Framework_TestCase;
use src\Scraper\Entity\ProductInfo;
use src\Scraper\Lib\ScraperLibrary;
use src\Scraper\Model\MasterScraper;
use src\Scraper\Model\ProductListItemScraper;
use src\Scraper\Model\ProductPageScraper;

class ListPageTests extends PHPUnit_Framework_TestCase
{

    public function testCanReachListPage()
    {
        $url = 'http://www.sainsburys.co.uk/webapp/wcs/stores/servlet/CategoryDisplay?listView=true&orderBy=FAVOURITES_FIRST&parent_category_rn=12518&top_category=12518&langId=44&beginIndex=0&pageSize=20&catalogId=10137&searchTerm=&categoryId=185749&listId=&storeId=10151&promotionId=#langId=44&storeId=10151&catalogId=10137&categoryId=185749&parent_category_rn=12518&top_category=12518&pageSize=20&orderBy=FAVOURITES_FIRST&searchTerm=&beginIndex=0&hideFilters=true';
        $client = new Client(['cookies' => true]);
        $response = $client->request('GET', $url);
        //response should replay with 200 status code
        $this->assertEquals(200, $response->getStatusCode());
    }


    public function testCanFindListString(){
        $testString = '<div><ul class="productLister "><li>item 1</li><li>item 2</li></ul></div>';
        $testOutcome = '<li>item 1</li><li>item 2</li>';

        $masterScraper = new MasterScraper();

        $this->assertEquals($testOutcome, $masterScraper->getProductListString($testString));
    }

    public function testCanScrapeUnitCost(){
        $testString = '<div><p class="pricePerUnit">1000<abbr title="per"></div>';
        $testOutcome = '1000';

        $productListItemScraper = new ProductListItemScraper($testString);
        $this->assertEquals($testOutcome, $productListItemScraper->scrapeUnitCost());
    }

    public function testCanScrapeLink(){
        $testString = '<div><a href="alink.com" >
	                                        A Link
        </a></div>';
        $testOutcome = 'alink.com';

        $productListItemScraper = new ProductListItemScraper($testString);
        $this->assertEquals($testOutcome, $productListItemScraper->scrapeLink());
    }

    public function testCanScrapeTitle(){
        $testString = '<div><a href="alink.com" >
	                                        A Link
        </a></div>';
        $testOutcome = 'A Link';

        $productListItemScraper = new ProductListItemScraper($testString);
        $this->assertEquals($testOutcome, $productListItemScraper->scrapeTitle());
    }

    public function testCanScrapeDescription(){
        $testString = '<div><h3>Description</h3><div>

        <p>line 1</p>
        <p>line 2</p>

        </div></div>';
        $testOutcome = 'line 1 line 2';

        $productInfo = new ProductInfo();
        $productPageScraper = new ProductPageScraper($productInfo);

        $this->assertEquals($testOutcome, $productPageScraper->scrapeProductDescription($testString));
    }

    public function testCanDecorateInfo(){
        $productInfo = new ProductInfo();
        $productInfo->setTitle('title');
        $productInfo->setSize('size');
        $productInfo->setDescription('description');
        $productInfo->setUnitCost('unit_price');

        $expected = [
            'title'=>'title'
            ,'size'=>'size'
            ,'unit_price'=>'unit_price'
            ,'description'=>'description'
        ];

        $this->assertEquals($expected, ScraperLibrary::productInfoArrayDecorator($productInfo));
    }
}
