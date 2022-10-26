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

 namespace GPS\GPSDataHandler;

abstract class GPSDataHandler {
    abstract public static function save(array $data);
    abstract public static function load();
} // end of class {GPSDataHandler







 ?>