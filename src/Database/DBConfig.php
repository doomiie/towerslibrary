<?php

/**
 * DBConfig 
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


namespace Database;

use Exception;

class DBConfig
{
    public static function load($fileName)
    {
        $string = file_get_contents($fileName);
        if ($string === false) {
            return null;
        }

        $decodeString = json_decode($string, true);
        if ($decodeString === null) {
            return null;
        }

        print_r($decodeString);
    }
}
