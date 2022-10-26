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


    
    class GPSTowerFake extends GPSTower {

        protected $wait = true;
        protected $verbose = false;
        protected $speed = 20000;

        public function wait()
        {
            if($this->wait) { usleep($this->speed); }
            $this->move(0.0001);
            if($this->verbose) printf("TOWER [%s] waiting one tick\n", $this->imei);
            return $this->showOnMap();
        }
        
        public function move(float $dist)
        {
            if($this->wait) { usleep($this->speed); }
            if($this->verbose) printf("TOWER [%s] moving distance %s\n", $this->imei, $dist);
            $res = GPSDistance::getNewLocation($this->ResultArray['LAT'],$this->ResultArray['LON'],$dist,0);
            $this->moveToGPS($res['LAT'],$res['LON']);
            return $this->showOnMap();
        }

        public function power(string $state)
        {
            if($this->wait) { usleep($this->speed); }
            if($this->verbose) printf("TOWER [%s] being powered %s\n", $this->imei, $state);
            if($state=='true') { $this->turnON(); } else { $this->turnOFF();}
            return $this->showOnMap();
        }

        public function showOnMap()
        {
            
            $result = $this->ResultArray['LAT'] . "," . $this->ResultArray['LON']  ;
            return '<a target="_blank" href="http://maps.google.com/maps?q='.$result.'">Dystans pokonany: '. number_format((float)(1000* $this->ResultArray['DIST']), 2, '.', '') .' metrów </a><br>';
        }

  
  
    } // end of class {GPSTower} 

?>