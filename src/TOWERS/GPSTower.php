<?php

/**
 * Tower prototype to be used within the project.
 * Loading data from database
 * Saving data to database
 * Receiving data from GPS
 * Sending 'fake' data to listen receiver
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

namespace TOWERS;

use GPS\GPSDistance;
use GPS\GPSValidate;

abstract class AbstractGPSTower
{

    private $ResultArray = array(
        'LAT' => '0',
        'LON' => '0',
        'DIST' => '0'
    );
    private $Active = false;
    /**
     * Moves tower by random direction, by given distance
     *
     * @param int $distance how far the tower should move
     * 
     * @return array new GPS coordinates + distance from previous position
     * 
     */
    abstract public function moveDistance(int $distance): array;
    /**
     * Moves tower to given coordinates
     *
     * @param int $distance how far the tower should move
     * 
     * @return array new GPS coordinates + distance from previous position
     * 
     */
    abstract public function moveToGPS(string $location1, string $location2): array;
    abstract public function turnON(): bool;
    abstract public function turnOFF(): bool;
    abstract public function isActive(): bool;
} // end of class {GPSDataHandler

class GPSTower
{
    protected $ResultArray = array(
        'LAT' => '0',
        'LON' => '0',
        'DIST' => '0'
    );
    protected  $Active = false;
    public $imei = "";
    public $serial_num = "";

    public function __construct($imei, $serial_num, $location1 = "", $location2 = "")
    {
        if (!$this->setLatLon($location1, $location2)) {
            return null;
        };
        $this->imei = $imei;
        $this->serial_num = $serial_num;
        $this->ResultArray['DIST'] = 0;
    }

    public function moveDistance(int $distance): array
    {
        return $this->ResultArray;
    }
    public function moveToGPS(string $lat, string $lon): array
    {
        $prevLat = $this->ResultArray['LAT'];
        $prevLon = $this->ResultArray['LON'];

        if (!$this->setLatLon($lat, $lon)) return null;
        $this->ResultArray['DIST'] = GPSDistance::calculateDistance4($lat, $lon, $prevLat, $prevLon);
        return $this->ResultArray;
    }
    public function turnON(): bool
    {
        return $this->Active = true;
    }
    public function turnOFF(): bool
    {
        return $this->Active = false;
    }
    public function isActive(): bool
    {
        return $this->Active;
    }

    private function setLatLon($location1, $location2)
    {
        //printf("Working with %s and %s\n", $location1, $location2);
        if (!GPSValidate::isGeoValid('latitude', $location1)) {
            return false;
        }
        if (!GPSValidate::isGeoValid('longitude', $location2)) {
            return false;
        }
        $this->ResultArray['LAT'] = $location1;
        $this->ResultArray['LON'] = $location2;
        return true;
    }
} // end of class {GPSTower} 
