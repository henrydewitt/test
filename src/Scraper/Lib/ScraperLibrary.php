<?php
/**
 * Created by PhpStorm.
 * User: behshana
 * Date: 28/09/2015
 * Time: 17:07
 */

namespace src\Scraper\Lib;


use src\Scraper\Entity\ProductInfo;

class ScraperLibrary
{
    /**
     * General function to convert into readable sizes
     * @param $size
     * @param int $precision
     * @return string
     */
    public static function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    /**
     * @param string $string
     * @param string $startMarker
     * @param string $endMarker
     * @return string
     */
    public static function findDelimitedContent($string,$startMarker,$endMarker){
        $startPos  = strpos ($string, $startMarker) + strlen($startMarker);
        $endPos  = strpos ($string, $endMarker);
        return substr ($string, $startPos , ($endPos-$startPos) );
    }

    /**
     * @param ProductInfo $productInfo
     * @return array
     */
    public static function productInfoArrayDecorator(ProductInfo $productInfo){
        return [
            'title'=>$productInfo->getTitle()
            ,'size'=>$productInfo->getSize()
            ,'unit_price'=>$productInfo->getUnitCost()
            ,'description'=>$productInfo->getDescription()
        ];
    }
}