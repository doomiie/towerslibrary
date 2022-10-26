<?php
/**
 * Tower Waiting Room
 * Wieża, która melduje się w systemie (imeii, serial), ale nie ma jej w bazie danych
 * 
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
namespace UserManagement;

use Database\DBObject;
use GPS\GPSLogic;

class TowerWaitingRoom extends \Database\DBObject
{
    protected $tableName = "tower_waiting_room";
    // field names should be THE SAME as database names!
    public $lat;
    public $lng;
    public $imei;
    public $serial_nr;
    public function print()
    {
        parent::print();
       
    }

    public function addToWaitingRoom($GPSLogic)
    {
        $myId = $this->findMe("imei", $GPSLogic->imei);
        
        
        $this->name = $GPSLogic->incomingIP;
        $this->imei = $GPSLogic->imei;
        $this->serial_nr = $GPSLogic->serial_nr;
        if($myId == DBObject::FINDME_NOT_FOUND)
        {
            $this->lat = $GPSLogic->lat;
            $this->currLat = $GPSLogic->lat;
            $this->lng = $GPSLogic->lng;
            $this->currLng = $GPSLogic->lng;
            error_log(sprintf("NEW TOWER %s \nFROM GPSLogic %s<br>\n",json_encode(array($this)), json_encode(array($GPSLogic))));
            $this->create();
            return $this->id;
        }
        if(count($myId))    // wieża już jest w poczekalni
        {
            //error_log(sprintf("mam  wieżę  %s<br>\n",json_encode($myId)));
            $this->load((int)$myId[0]['id']);
            $this->lat = $GPSLogic->lat;
            $this->lng = $GPSLogic->lng;
            $this->update();
            return $this->id;
        }
     

    }
}
