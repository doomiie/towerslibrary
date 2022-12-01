<?php

/**
 * GPS Distance meter
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

class GPSDistance
{
    /**
     * Oblicza odległość pomiędzy dwoma punktami
     *
     * @param mixed $location1 w postaci 52.4482367,16.8852368,14
     * @param mixed $location2 w postaci 52.4482367,16.8852368,14
     * 
     * @return float zwraca odległość w metrach
     * 
     */
    public static function calculateDistance($location1, $location2)
    {
        $loc1 = explode(",", $location1);
        $loc2 = explode(",", $location2);
        if (!GPSValidate::isGeoValid('latitude', $loc1[0])) {
            return 0;
        }
        if (!GPSValidate::isGeoValid('longitude', $loc1[1])) {
            return 0;
        }
        if (!GPSValidate::isGeoValid('latitude', $loc2[0])) {
            return 0;
        }
        if (!GPSValidate::isGeoValid('longitude', $loc2[1])) {
            return 0;
        }

        return self::twoPoints((float)$loc1[0], (float)$loc1[1], (float)$loc2[0], (float)$loc2[1]);
    }

    public static function calculateDistance4($lat1, $lon1, $lat2, $lon2)
    {
        //error_log(sprintf("Liczę dystans\n dla %s, %s, %s, %s", $lat1, $lon1, $lat2, $lon2));
        if (!GPSValidate::isGeoValid('latitude', $lat1)) {
            return -1;
        }
        if (!GPSValidate::isGeoValid('longitude', $lon1)) {
            return -2;
        }
        if (!GPSValidate::isGeoValid('latitude', $lat2)) {
            return -3;
        }
        if (!GPSValidate::isGeoValid('longitude', $lon2)) {
            return -4;
        }
       //error_log(sprintf("Liczę dystans\n dla %s, %s, %s, %s", round($lat1,5), round($lon1,5), round($lat2,5), round($lon2,5)));
        return self::twoPoints(round($lat1,6),round($lon1,6),round($lat2,6),round($lon2,6));
    }

    /**
     * [Description for twoPoints] Zwraca dystans pomiędzy dwoma punktami - w KM!
     *
     * @param mixed $latitudeFrom
     * @param mixed $longitudeFrom
     * @param mixed $latitudeTo
     * @param mixed $longitudeTo
     * 
     * @return [float] kilometry, np: 0.29966294578658176 (300 metrów)
     * 
     */
    private static function twoPoints(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo
    ) {


        $long1 = deg2rad($longitudeFrom);
        $long2 = deg2rad($longitudeTo);
        $lat1 = deg2rad($latitudeFrom);
        $lat2 = deg2rad($latitudeTo);

        //Haversine Formula
        $dlong = $long2 - $long1;
        $dlati = $lat2 - $lat1;
        //error_log(sprintf("DLONG %s, DLAT %s", $dlong, $dlati));
        
        $val = pow(sin($dlati / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($dlong / 2), 2);
        
        $res = 2 * asin(sqrt($val));
        //error_log(sprintf("VAL %s", $res));

        $radius = 3958.756;

        $kms = 1.609344;
        $dist = round(($res * $radius * $kms * 1000),2);    // dystnas w metrach
        return $dist;
    }
    // This code is contributed by akash1295
    // https://auth.geeksforgeeks.org/user/akash1295/articles


    public static function getNewLocation($lat, $lon, $toNorth, $toEast)
    {
        $oneDegreeKm = 111.32;

        $deltaLat = $toNorth / $oneDegreeKm;
        $deltaLon = $toEast / ($oneDegreeKm * cos(M_PI * $lat / 180));

        return array('LAT' => $lat + $deltaLat,'LON' => $lon + $deltaLon);
    }

    public static function getDistanceTwoObjects($object1, $object2, $from="")
    {
        //error_log(sprintf("Liczę dystans [%s] dla %s, %s, \n",$from,  json_encode($object1), json_encode($object2)));
        return GPSDistance::calculateDistance4($object1->lat, $object1->lng,$object2->lat, $object2->lng);
    }
}
