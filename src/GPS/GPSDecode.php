<?php

/**
 * GPS Decode data from TELTONIKA RUT955
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

class GPSDecode
{

    public static function calcDECtoDMS($dec, $longlat)
    {
        if ($longlat == 'lattitude') {
        $latDeg = (int)$dec;
        $latMin = ((($dec - $latDeg) * 3600) / 60);
        return $latDeg . $latMin;
        }
        if ($longlat == 'longitude') {
            $lngDeg  = (int)$dec;
            $lngMin = ((($dec - $lngDeg) * 3600) / 60);
            if($lngDeg < 100) $lngDeg = '0' . $lngDeg;
            return $lngDeg . $lngMin;
        }
        
    }
    private function calcDMStoDEC($dms, $longlat)
    {

        if ($longlat == 'lattitude') {
            $deg = substr($dms, 0, 2);
            $min = substr($dms, 2, 8);
        }
        if ($longlat == 'longitude') {
            $deg = (float)substr($dms, 0, 3);
            $min = (float)substr($dms, 3, 8);
        }

        return $deg + ((($min * 60)) / 3600);
    }

    /**
     * Fukcja pobiera z POST imei i serial_num dla telfoniki
     * UWAGA, długość imei to 15 znaków, serial_num to 10 znaków, inaczej się wykłada!
     *
     * @param mixed $gps strig wejściowy
     * 
     * @return array [imei][serial_num]
     * 
     */
    public static function restoreIDs($gps)
    {
        $imeipos = strpos($gps, 'imei=', 0);
        $result1 = substr($gps, $imeipos + 5, 15);
        $serialNum = strpos($gps, 'serial_num=', 0);
        $result2 = substr($gps, $serialNum+11, 10);
        $server = $_SERVER['REMOTE_ADDR'];
        return array('imei' => $result1, 'serial_nr' => $result2, 'remote_addr' => $server);
    }

    public static function restoreGPS($gps)
    {
        $gPos = strpos($gps, 'GPRMC', 0);
        $gps = substr($gps, $gPos);
        //echo $gps;
        if ($gps) {
            //echo"w1";
            $buffer = $gps;
            if (substr($buffer, 0, 5) == 'GPRMC') {
                // echo"w2";
                $gprmc = explode(',', $buffer);

                $data1 = self::calcDMStoDEC($gprmc[3], 'lattitude');
                $data2 = self::calcDMStoDEC($gprmc[5], 'longitude');

                $data = $data1 . ',' . $data2;
            }
        }
        return $data;
    }


    public static function getRawHTTPRequest()
    {

        $request = "$_SERVER[REQUEST_METHOD] $_SERVER[REQUEST_URI] $_SERVER[SERVER_PROTOCOL]\r\n";
        //$request = "";
        foreach (getallheaders() as $name => $value) {
            $request .= "$name: $value\r\n";
        }

        $request .= "\r\n" . file_get_contents('php://input');
        //error_log($request);

        return $request;
    }
}// koniec klasy GPSDecode
