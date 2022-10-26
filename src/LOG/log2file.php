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

 namespace LOG;

use Exception;

class log2file {

    protected static $userid = null;
    protected static $instance = null;

    private function __construct() {
        // Nothing to do - there are no instances.
    }

    

    private static function init() {
        if (is_null(self::$userid)) {
            //self::$didInit = true;
            throw new Exception("SET USER first!", 1);
            
            // one-time init code.
        }
    }

    public static function setUser($userid)
    {
        self::$userid = $userid;

    }



    public static function user($data)
    {
        Log2file::init();
        return self::save($data, "user");
    }
    public static function debug($data)
    {
        Log2file::init();
        return self::save($data, "debug");
    }


    private static function save($data, $prefix="")
    {
        
        $save = json_encode($data);
        $logfile = $_SERVER['DOCUMENT_ROOT'] . "/log/" . $prefix . date("-Y-m-d-") . self::$userid . ".log";
        //$logfile =  . self::$userid . ".log";
        //error_log("GPS DATA: " . $save . "in logfile: " . $logfile . "in dir " . __DIR__);
        return error_log(sprintf("[%s] %s \n",date("Y-m-d h:n:s"),  $save) ,3,  $logfile );
        
    }

} // end of class {GPSDataJsonFile } extends GPSDataHandler} 
