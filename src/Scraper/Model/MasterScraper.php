<?php
/**
 * Created by PhpStorm.
 * User: behshana
 * Date: 28/09/2015
 * Time: 17:05
 */

namespace src\Scraper\Model;

use GuzzleHttp\Client;
use src\Scraper\Lib\ScraperLibrary;

class MasterScraper{

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $listStartMarker = '<ul class="productLister ">';

    /**
     * @var string
     */
    private $listEndMarker = '</ul>';

    /**
     * @return String
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param String $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getListStartMarker()
    {
        return $this->listStartMarker;
    }

    /**
     * @param string $listStartMarker
     */
    public function setListStartMarker($listStartMarker)
    {
        $this->listStartMarker = $listStartMarker;
    }

    /**
     * @return string
     */
    public function getListEndMarker()
    {
        return $this->listEndMarker;
    }

    /**
     * @param string $listEndMarker
     */
    public function setListEndMarker($listEndMarker)
    {
        $this->listEndMarker = $listEndMarker;
    }

    public function __construct($url = null){
        if($url){
            $this->setUrl($url);
        }
    }

    public function scrape(){

        //use Guzzle to get page details (cookies on for js load)
        $client = new Client(['cookies' => true]);
        $response = $client->request('GET', $this->getUrl());

        //get the product list string from the page body
        $productList = $this->getProductListString($response->getBody());

        //convert list to array (removing first element as its before the list elements)
        $productListArray = explode('<li>',$productList);
        array_shift($productListArray);

        $totalCost = 0;
        $finalArray=[];

        //for each product in list scrape the data and add to total
        foreach($productListArray as $listEntry){

            //send list item html to list scraper which creates a ProductInfo object
            $productListItemScraper = new ProductListItemScraper($listEntry);
            $productListItemScraper->scrape();

            //send ProductInfo object to page scraper
            //which uses the link found in the list item to get further information
            $productPageScraper = new ProductPageScraper($productListItemScraper->getProductInfo());
            $productPageScraper->scrape();

            //and finally get complete productInfo object
            $productInfo = $productPageScraper->getProductInfo();

            //add cost to running total and get formatted array from info object
            $totalCost = $totalCost+ floatval($productInfo->getUnitCost());
            $finalArray[] = ScraperLibrary::productInfoArrayDecorator($productInfo);
        }

        return ['results'=>$finalArray,'total'=>$totalCost];
    }

    public function getProductListString($string){

        //find position of start of list html string
        $listStart  = strpos ($string, $this->getListStartMarker())+strlen($this->getListStartMarker());

        //find position of end of list html string
        $listEnd  = strpos ($string, $this->getListEndMarker(),$listStart);

        //return list string
        return substr ($string, $listStart , ($listEnd-$listStart) );
    }

}