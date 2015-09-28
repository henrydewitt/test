<?php

/**
 * scraper to get information from a product list item
 *
 * scraper works by looking for strings that are unique to the stand and end of the required information
 *
 * has a few assumptions:
 * first that all items in the list are formatted in the same way
 * second that the first link in list item html is to the product's page
 * third that the title of product is contained in the link html element
 * forth that unit cost is the first string in the element with class "pricePerUnit"
 *
 */

namespace src\Scraper\Model;

use src\Scraper\Lib\ScraperLibrary;
use src\Scraper\Entity\ProductInfo;

class ProductListItemScraper
{

    /**
     * @var ProductInfo
     */
    private $productInfo;

    /**
     * @var string
     */
    private $string;

    /**
     * @var string
     */
    private $linkStartMarker = '<a href="';

    /**
     * @var string
     */
    private $linkEndMarker = '" >';

    /**
     * @var string
     */
    private $titleEndMarker = '<';

    /**
     * @var string
     */
    private $costStartMarker =  '<p class="pricePerUnit">';

    /**
     * @var string
     */
    private $costEndMarker = '<abbr title="per">';

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

    /**
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * @param string $string
     */
    public function setString($string)
    {
        $this->string = $string;
    }

    /**
     * @return string
     */
    public function getLinkStartMarker()
    {
        return $this->linkStartMarker;
    }

    /**
     * @param string $linkStartMarker
     */
    public function setLinkStartMarker($linkStartMarker)
    {
        $this->linkStartMarker = $linkStartMarker;
    }

    /**
     * @return string
     */
    public function getLinkEndMarker()
    {
        return $this->linkEndMarker;
    }

    /**
     * @param string $linkEndMarker
     */
    public function setLinkEndMarker($linkEndMarker)
    {
        $this->linkEndMarker = $linkEndMarker;
    }

    /**
     * @return string
     */
    public function getTitleEndMarker()
    {
        return $this->titleEndMarker;
    }

    /**
     * @param string $titleEndMarker
     */
    public function setTitleEndMarker($titleEndMarker)
    {
        $this->titleEndMarker = $titleEndMarker;
    }

    /**
     * @return string
     */
    public function getCostStartMarker()
    {
        return $this->costStartMarker;
    }

    /**
     * @param string $costStartMarker
     */
    public function setCostStartMarker($costStartMarker)
    {
        $this->costStartMarker = $costStartMarker;
    }

    /**
     * @return string
     */
    public function getCostEndMarker()
    {
        return $this->costEndMarker;
    }

    /**
     * @param string $costEndMarker
     */
    public function setCostEndMarker($costEndMarker)
    {
        $this->costEndMarker = $costEndMarker;
    }


    /**
     * set string to be scraped in construct and
     * create object for product info
     * @param string $string
     */
    public function __construct($string){
        $this->setString($string);
        $this->setProductInfo(new ProductInfo());
    }

    /**
     * scraper to find unit cost from product list item
     */
    public function scrapeUnitCost(){
        $cost = ScraperLibrary::findDelimitedContent(
            $this->getString()
            ,$this->getCostStartMarker()
            ,$this->getCostEndMarker()
        );

        return trim ( $cost, " \r\n£");
    }

    /**
     * scraper to find link to item page from product list item
     */
    public function scrapeLink(){
            return ScraperLibrary::findDelimitedContent(
                $this->getString()
                ,$this->getLinkStartMarker()
                ,$this->getLinkEndMarker()
            );
    }

    /**
     * scraper to find title from product list item
     */
    public function scrapeTitle(){
        //find start of link
        $linkStart  =
            strpos (
                $this->getString()
                ,$this->getLinkStartMarker()
            )
            + strlen($this->getLinkStartMarker());

        //find end of link (starting after start of link position)
        $linkEnd =
            strpos (
                $this->getString()
                ,$this->getLinkEndMarker()
                ,$linkStart
            )
            + strlen($this->getLinkEndMarker());

        //find the end of the title position (starting after end of link position)
        $titleEnd = strpos (
            $this->getString()
            ,$this->getTitleEndMarker()
            ,$linkEnd
        );

        //clean title string
        $title = substr ($this->getString(), $linkEnd , ($titleEnd-$linkEnd) );
        return trim ( $title, " \r\n\t");
    }


    /**
     * perform all scrape actions and set data in productInfo object
     */
    public function scrape(){
        $this->getProductInfo()->setUnitCost($this->scrapeUnitCost());
        $this->getProductInfo()->setLink($this->scrapeLink());
        $this->getProductInfo()->setTitle($this->scrapeTitle());
    }

}