<?php

/**
 * SIMPLE log per immei logging
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

 namespace LOG;

use Exception;
class GPSlog  {

    public static function error_log($GPSLogic, $tower, $message)
    {
        error_log("$message > $tower->imei\n",3,'log/' . $tower->imei . '.log');
    }
} // end of class {GPSlog} 
?>