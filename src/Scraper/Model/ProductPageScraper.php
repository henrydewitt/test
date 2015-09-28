<?php

/**
 * scraper to get the information from a products pages
 *
 * a few assumptions:
 * first that the product description is preceded by the heading Description
 * second that the heading is followed by a div containing only the description, line breaks and '<p>','</p>' html elements
 */

namespace src\Scraper\Model;

use GuzzleHttp\Client;
use src\Scraper\Entity\ProductInfo;
use src\Scraper\Lib\ScraperLibrary;

class ProductPageScraper
{

    /**
     * @var ProductInfo
     */
    private $productInfo;

    /**
     * @var string
     */
    private $descriptionTitleMarker = 'Description</h3>';

    /**
     * @var string
     */
    private $descriptionStartMarker = '>';

    /**
     * @var string
     */
    private $descriptionEndMarker = '</div>';

    /**
     * @var string
     */
    private $pageBodyString;

    /**
     * @return string
     */
    public function getPageBodyString()
    {
        return $this->pageBodyString;
    }

    /**
     * @param string $pageBodyString
     */
    public function setPageBodyString($pageBodyString)
    {
        $this->pageBodyString = $pageBodyString;
    }

    /**
     * @return string
     */
    public function getDescriptionTitleMarker()
    {
        return $this->descriptionTitleMarker;
    }

    /**
     * @param string $descriptionTitleMarker
     */
    public function setDescriptionTitleMarker($descriptionTitleMarker)
    {
        $this->descriptionTitleMarker = $descriptionTitleMarker;
    }

    /**
     * @return string
     */
    public function getDescriptionStartMarker()
    {
        return $this->descriptionStartMarker;
    }

    /**
     * @param string $descriptionStartMarker
     */
    public function setDescriptionStartMarker($descriptionStartMarker)
    {
        $this->descriptionStartMarker = $descriptionStartMarker;
    }

    /**
     * @return string
     */
    public function getDescriptionEndMarker()
    {
        return $this->descriptionEndMarker;
    }

    /**
     * @param string $descriptionEndMarker
     */
    public function setDescriptionEndMarker($descriptionEndMarker)
    {
        $this->descriptionEndMarker = $descriptionEndMarker;
    }

    /**
     * @return ProductInfo
     */
    public function getProductInfo()
    {
        return $this->productInfo;
    }

    /**
     * @param ProductInfo $productInfo
     */
    public function setProductInfo($productInfo)
    {
        $this->productInfo = $productInfo;
    }

    public function __construct(ProductInfo $productInfo){
        $this->setProductInfo($productInfo);
    }

    /**
     * product description is identifies by its heading in html
     * description is the text within the div immediately following the heading element
     *
     * @param $string
     * @return mixed|string
     */
    public function scrapeProductDescription($string){
        //find the end of the description title element
        $descriptionElementStart  = strpos ($string
                ,$this->getDescriptionTitleMarker()
            ) + strlen($this->getDescriptionTitleMarker());

        //find start of description text
        $descriptionStart  = strpos ($string
                ,$this->getDescriptionStartMarker()
                ,$descriptionElementStart
            )+ strlen($this->getDescriptionStartMarker());

        //find end of description text
        $descriptionEnd  = strpos ($string
            , $this->getDescriptionEndMarker()
            ,$descriptionStart);

        //get the substring and remove and extra chars
        $description = substr ($string, $descriptionStart , ($descriptionEnd-$descriptionStart) );
        $description = str_replace(['<p>','</p>'],'',$description);
        $description = preg_replace( "/\s+/", " ", $description );
        return trim ( $description, " \r\n\t");
    }

    /**
     * scrape description from product page and get page size
     *
     * @return ProductInfo
     */
    public function scrape(){
        //get response to page request
        $client = new Client(['cookies' => true]);
        $response = $client->request('GET', $this->getProductInfo()->getLink());

        //try to get size from header, if not found fall back to calculating from page body
        if(!$size = $response->getHeader('Content-Length')){
            $size = $response->getBody()->getSize();
            $size = ScraperLibrary::formatBytes($size,1);
        }
        $this->getProductInfo()->setSize($size);

        $this->getProductInfo()->setDescription($this->scrapeProductDescription($response->getBody()));

        return $this->getProductInfo();
    }
}