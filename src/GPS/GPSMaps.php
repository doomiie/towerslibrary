<?php

/**
 * GPS Map handler
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

class GPSMaps
{

    

    public static function getGoogleMapsLink($lat, $lng)
    {
        return "https://maps.google.com/maps?q=".$lat.",".$lng."";
    }

}// koniec klasy GPSMaps
