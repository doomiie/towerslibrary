<?php
/**
 * GPS Data validation
 * 
 * @see       https://github.com/doomiie/gps/

 *
 *
 * @author    Jerzy Zientkowski <jerzy@zientkowski.pl>
 * @copyright 2020 - 2022 Jerzy Zientkowski
 

 * @license   FIXME need to have a licence
 * @note      This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */
namespace GPS;

class GPSValidate
{
    /**
     * Waliduje poprawność danych GPS     
     *
     * 
     */
    /*
 * The valid range:
 *     - in degrees
 *         - latitude -90 and +90
 *         - longitude -180 and +180
 *     - in decimals
 *         - latitude precision=10, scale=8
 *         - longitude precision=11, scale=8
 */
public static function isGeoValid($type, $value)
{
    $value = substr($value,0,10);   // max 10 precision!
    $pattern = ($type == 'latitude')
        ? '/^(\+|-)?(?:90(?:(?:\.0{1,8})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,8})?))$/'
        : '/^(\+|-)?(?:180(?:(?:\.0{1,8})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,8})?))$/';
    
    if (preg_match($pattern, $value)) {
        return true;
    } 
    return false;
}
}
