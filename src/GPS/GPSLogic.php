<?php

/**
 * GPS LOGIC object, to process data
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
 * 
 * 
 * 
 */

namespace GPS;

use UserManagement\Tower;
use UserManagement\TowerSetting;
use \DateTime;
use Log\GPSlog;
use UserManagement\HomeBase;
use UserManagement\TowerStatus;
use UserManagement\Project;

class GPSLogic
{
    public $loadedData;   // tu trzymamy oryginalne dane
    public $ids;            // tu trzymamy imei i serialnr
    public $lat;
    public $lng;
    public $imei;
    public $serial_nr;
    public $incomingIP;

    const LOADDATA_TOWER_NOT_FOUND = -1;
    const GPSLOGIC_NO_NEW_DATA = -2;
    const GPSLOGIC_NO_PROJECT_FOUND = -4;


    public function loadData(string $data)
    {
        //error_log(sprintf("Loading data id %s<br>\n", $data));
        $this->loadedData = GPSDecode::restoreGPS($data);
        $lat_lng = explode(",", $this->loadedData);
        $this->lat = $lat_lng[0];
        $this->lng = $lat_lng[1];
        $this->ids = GPSDecode::restoreIDs($data);
        $this->imei = $this->ids['imei'];
        $this->serial_nr = $this->ids['serial_nr'];
        $this->incomingIP = $this->ids['remote_addr'];
        //error_log(sprintf("Looking for imei: %s<br>\n", $this->ids['imei']));

        // FIXME nie mam znajdowania po imei/serial_nr
        //return;
        $tower = new Tower();
        $id = $tower->findMe('imei', $this->ids['imei']);
        //error_log(sprintf("Looking for row: %s<br>\n", json_encode($id)));
        if (is_array($id)) return $id;
        return GPSLogic::LOADDATA_TOWER_NOT_FOUND;


        //if(empty($id)) 
        //error_log(sprintf("Loading data id %s<br>\n", json_encode($id[0]['id'])));
        //error_log(sprintf("TOWER NOT FOUND %s<br>\n", json_encode($id[0]['id'])));

        //$tower->load((int)$id[0]['id']);
        //$tower->print();

    }


    private function getLastGPS($tower)
    {
        $gpsEntry = (new GPSDBObject())->returnLast(GPSDBObject::REFTYPE_TOWER, $tower->id);
        //printf("Last entry for tower %s is %s\n", $tower->name, json_encode($gpsEntry));

        $this->lat = $gpsEntry['lat'];
        $this->lng = $gpsEntry['lng'];

        return $gpsEntry;
    }


    /**
     * Odpalana zdalnie, ta funkcja iteruje wszystkie wieże i sprawdza ich status
     *
     * @return [type]
     * 
     */
    public function runEngine()
    {
        $towerList = (new Tower())->list('true');
        //error_log(sprintf("Tower list is %s<br>\n", json_encode($towerList)));

        foreach ($towerList as $key => $value) {
            # code...
            //printf("Tower list  %s is %s<br>\n",$key, json_encode($value));
            $tower = new Tower((int)$value['id']);
            if ($tower->imei != $this->ids['imei']) {
                //\LOG\GPSLog::error_log($this, $tower, sprintf("Wrong tower (imei: %s) found  %s <br>\n",$tower->imei, $this->ids['imei']));
                //error_log(sprintf("Wrong tower (imei: %s) found  %s <br>\n",$tower->imei, $this->ids['imei']));
                continue;
            }
            //\LOG\GPSLog::error_log($this, $tower, sprintf("Good tower (imei: %s) found  %s <br>\n",$tower->imei, $this->ids['imei']));
            $towerSetting = new TowerSetting(null, $tower);
            //error_log(sprintf("Wrong tower (imei: %s) found  %s <br>\n",$tower->imei, $this->ids['imei']));
            //error_log(sprintf("Tower setting is  (id: %s) found  %s <br>\n",$towerSetting->id, $this->ids['imei']));
            if ($towerSetting->id == -1) {
                return -1;
            }

            $towerStatus = new TowerStatus($tower);

            /**!SECTION
             * Pierwsza rzecz do sprawdzenia, czy wieża była widziana ostatnio :)
             */
            if ($this->checkLastSeen($tower, $towerSetting, $towerStatus)) {
                error_log(sprintf("Problem z wieżą, nie była widziana od dawna (imei: %s)<br>\n", $tower->imei));
            };
            /**!SECTION
             * Druga rzecz do sprawdzenia, czy się ruszała o określony dystans
             */
            $distance = $this->checkMoved($tower, $towerSetting, $towerStatus);
            if (-1 == $distance) {
                error_log(sprintf("Problem z wieżą, poruszyła się o więcej, niż zakładane (imei: %s)<br>\n", $tower->imei));
            }
            /**!SECTION
             * Sprawdzamy, czy nie jesteśmy w homebase
             */
            $this->checkHomeBase($tower, $towerSetting, $towerStatus);
            /**!SECTION
             * Kolejna rzecz - czy odjechaliśmy od punktu startowego!
             */
            $distance = $this->checkMovedFromStart($tower, $towerSetting, $towerStatus);
            if (-1 == $distance) {
                error_log(sprintf("Problem z wieżą, odjechała od punktu startowego, niż zakładane (imei: %s)<br>\n", $tower->imei));
            }


            /**
             * Zapisujemy ostatnią pozycję do historii, w zależności od towerSetting->keyFrame (!)
             * FIXME nie ma tutaj jeszcze keyFrame, zapisuję KAŻDY zgłoszony GPS, to rozwali bazę danych (100 wież * 600 zapisów na godzinę)
             */
            $gpsupdate = new GPSDBObject();
            $gpsupdate->lat = $this->lat;
            $gpsupdate->lng = $this->lng;
            $gpsupdate->refType = GPSDBObject::REFTYPE_TOWER;
            $gpsupdate->refID = $tower->id;
            $gpsupdate->create();


            $towerSetting->lastSeen  = $towerStatus->lastSeen;
            $towerSetting->update();
            // aktualizacja CURR bieżącej wieży
            $tower->currLat = $this->lat;
            $tower->currLng = $this->lng;
            $tower->lastSeen = $towerSetting->lastSeen;
            $tower->update();



            $towerStatus->lastSeen =  (new \DateTime())->Format("Y-m-d H:i:s");
            $towerStatus->update();
        }
        return 1;
    }

    /**
     * Funkcja sprawdza, czy wieża się ruszyła
     * Porównuje ostatni wpis dla wieży z bieżącym lat, lng w swoich wartościach 
     * Następnie wpisuje do tabeli gps
     *
     * @param mixed $tower
     * 
     * @return -1 jeśli wieża się poruszyła powyżej treshold, int w przeciwnym wypadku
     * 
     */
    public function checkMoved($tower, $towerSetting, $towerStatus)
    {
        $gpsobj = new GPSDBObject();
        $lista = $gpsobj->listWhere('true', "refType = 1 AND refID = $tower->id ORDER BY time_added DESC LIMIT 1");
        //echo json_encode($lista);
        $dist = 1000 * GPSDistance::calculateDistance4($this->lat, $this->lng, $lista[0]['lat'], $lista[0]['lng']);

        //error_log(sprintf("DISTANCE! %d is %s <br>\n", $towerSetting->distanceMoved, $dist));
        $towerStatus->distanceMoved = $dist;
        if ($dist > $towerSetting->distanceMoved) {
            //printf("ALERT! %d is %s <br>\n",$towerSetting->distanceMoved, $dist);
            $towerStatus->flagMoved = true;
            return -1;
        }

        return $dist;
    }

    public function checkMovedFromStart($tower, $towerSetting, $towerStatus)
    {
        $gpsobj = new GPSDBObject();
        $lista = $gpsobj->listWhere('true', "refType = 1 AND refID = $tower->id ORDER BY time_added DESC LIMIT 1");
        //echo json_encode($lista);
        $dist = 1000 * GPSDistance::calculateDistance4($this->lat, $this->lng, $towerSetting->startLat, $towerSetting->startLng);

        //error_log(sprintf("DISTANCE! %d is %s <br>\n", $towerSetting->distanceMovedFromStart, $dist));
        $towerStatus->distanceMovedFromStart = $dist;
        if ($dist > $towerSetting->distanceMovedFromStart) {
            $towerStatus->flagMovedStart = true;
            //printf("ALERT! %d is %s <br>\n",$towerSetting->distanceMoved, $dist);
            return -1;
        }

        return $dist;
    }

    /**
     * Funkcja sprawdza, jaki jest czas pomiędzy ostatnim "widzeniem" wieży a obecnymc zasem i, jeśli jest >od idleTime w settings - zwraca true
     *
     * @param mixed $tower
     * 
     * @return [type]
     * 
     */
    public function checkLastSeen($tower, $towerSetting, $towerStatus)
    {

        if (is_null($towerSetting)) return false;
        printf("TOWER was last seen %s <br>\n", $towerSetting->lastSeen);
        printf("TOWER status has %s <br>\n", $towerStatus->lastSeen);
        printf("TOWER status idle is %s <br>\n", $towerStatus->statusIdleTime);
        // sprawdź różnicę pomiędzy czasem lastseen a teraz:
        $now = new  DateTime();
        $lastSeen = DateTime::createFromFormat("Y-m-d H:i:s", $towerSetting->lastSeen);
        //var_dump(\DateTime::getLastErrors());
        printf("NOW vs THEN  is %s vs  <br>\n", $lastSeen->Format("Y-m-d H:i:s"));
        $diffInSeconds = $now->getTimestamp() - $lastSeen->getTimestamp();
        error_log(sprintf("DIFF  is %s <br>\n", $diffInSeconds));
        $towerStatus->statusIdleTime = $diffInSeconds;
        if ($diffInSeconds > $towerSetting->idleTime) {
            //printf("ALERT! %d is %s <br>\n",$towerSetting->idleTime, $diffInSeconds);
            $towerStatus->flagLastSeen = true;
            return true;    // true = mamy overtime
        }
        //error_log(sprintf("NOW vs THEN [%d] is %s vs %s <br>\n", $diffInSeconds, $now->Format("Y-m-d H:i:s"), $lastSeen->Format("Y-m-d H:i:s")));
        return false;
    }

    
    public function checkHomeBase($tower, $towerSetting, $towerStatus)
    {
        // toiwer jeszcze nie jest zaktualizowana, trzeba pamiętać!
        $project = new Project();
        $row = $tower->checkMeIn("tower_project");
        if (is_string($row)) {
            return self::GPSLOGIC_NO_PROJECT_FOUND;
        }
        $project->load((int)$row[0]['project_id']);

        $homeBaseList = $project->checkMeIn("homebase_project");
        foreach ($homeBaseList as $key => $value) {
            # odpal homeBase i sprawdź, czy wieża jest w home base
            $homeBase = new HomeBase((int)$value['homebase_id']);
            $dist = 1000* GPSDistance::getDistanceTwoObjects( $homeBase, $this, __FUNCTION__);
            //error_log(sprintf("DISTANCE HB! %d is %s <br>\n", 0, $dist));
            /**!SECTION
             * Scenariusze
             * 1. Wieża jest w magazynie i była w magazynie
             */
            if($dist < $towerSetting->distanceFromHomeBase && $towerStatus->flagEnteredHomeBase)
            {
                error_log(sprintf("HB: In HB, not moved"));
            }
            else
            if($dist < $towerSetting->distanceFromHomeBase && !$towerStatus->flagEnteredHomeBase)
            {                
                $towerStatus->setFlag("flagEnteredHomeBase",true);
                error_log(sprintf("HB: In HB, moved from outside"));
                // RAISE ALERT, weszliśmy do magazynu
            }
            else
            if($dist > $towerSetting->distanceFromHomeBase && $towerStatus->flagEnteredHomeBase)
            {
                $towerStatus->setFlag("flagLeftHomeBase",true);
                error_log(sprintf("HB: In HB, moved from outside"));
                // RAISE ALERT, weszliśmy do magazynu
            }
            else
            {
                
            }
        $towerStatus->distanceFromHomeBase = $dist;
        $towerStatus->update();

            
        }
    }

    // FIXME Dokończyć tę funkcję
    public function runEmptyEngine()
    {
        $towerList = (new Tower())->list();
        //error_log(sprintf("Tower list is %s<br>\n", json_encode($towerList)));

        foreach ($towerList as $key => $value) {
            # code...
            //printf("Tower list  %s is %s<br>\n",$key, json_encode($value));
            $tower = new Tower((int)$value['id']);
            $this->runEngineForTower($tower);
        }
    } // end of tunEmptyEngine()



    /**
     * Single run function for tower object
     * Checks against
     * 1. if settings exist
     * 2. when the tower was last seen
     * 3. if there's new data for the tower (if none, return)
     * 4. if the tower is in the home base
     
     *
     * @param mixed $tower
     * 
     * @return [type]
     * 
     */
    public function runEngineForTower($tower)
    {
        //$gpsEntry = $this->getLastGPS($tower);
        $towerSetting = new TowerSetting(null, $tower);
        if ($towerSetting->id == -1) {
            return;
        }    // no settings, this tower won't be checked
        $towerStatus = new TowerStatus($tower);
        $newData = $this->checkNewData($towerStatus, $tower);

        $this->checkLastSeen($tower, $towerSetting, $towerStatus);

        if ($newData === false) {
            printf("Aktualizacja towerStatus");
            $towerStatus->update();
            return self::GPSLOGIC_NO_NEW_DATA;                          // no new data, don't bother...but we need lastSeen
        }
        printf("Nowe date z GPS: %d\n", $newData);

        $distance = $this->checkMoved($tower, $towerSetting, $towerStatus);
        $this->checkHomeBase($tower, $towerSetting, $towerStatus);
        $distance = $this->checkMovedFromStart($tower, $towerSetting, $towerStatus);

        $towerStatus->update();
        $towerSetting->update();
    }

    private function checkNewData(TowerStatus $towerStatus, Tower $tower)
    {

        $gpsEntry = $this->getLastGPS($tower);
        //printf("%s: date z GPS: %s vs %s\n", __FUNCTION__, $gpsEntry['time_added'] , $towerStatus->lastSeen);
        if ($gpsEntry['time_added'] == $towerStatus->lastSeen)
            return false;
        return true;
    }
}
