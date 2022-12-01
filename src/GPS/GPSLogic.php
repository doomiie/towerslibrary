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
use UserManagement\TowerWaitingRoom;
use \DateTime;
use GoogleSheetTower\GoogleSheetLogic;
use Log\GPSlog;
use UserManagement\HomeBase;
use UserManagement\TowerStatus;
use UserManagement\Project;
use LOG\user_log;

class GPSLogic
{
    public $loadedData;   // tu trzymamy oryginalne dane
    public $ids;            // tu trzymamy imei i serialnr
    public $lat;
    public $lng;
    public $imei;
    public $serial_nr;
    public $incomingIP;

    public $tower_id;   // do przekazania na zewnątrz
    const GPSLOGIC_ENGINE2_SUCCESS = 2;     // funkcja zakończona sukcesem
    const LOADDATA_TOWER_NOT_FOUND = -1;
    const GPSLOGIC_NO_NEW_DATA = -2;
    const GPSLOGIC_NO_PROJECT_FOUND = -4;
    // FIXME trzeba ogarnąć losowe dane, jeśli coś pójdzie nie tak, albo podłączymy inny modem!
    public function loadData(string $data)
    {
        //error_log(sprintf("Loading data id %s<br>\n", $data));
        $this->loadedData = GPSDecode::restoreGPS($data);
        $lat_lng = explode(",", $this->loadedData);
        //error_log(sprintf("Loading data id %s<br>\n", json_encode($lat_lng)));
        $this->lat = $lat_lng[0];
        $this->lng = $lat_lng[1];
        $this->ids = GPSDecode::restoreIDs($data);
        $this->imei = $this->ids['imei'];
        $this->serial_nr = $this->ids['serial_nr'];
        $this->incomingIP = $this->ids['remote_addr'];
        //error_log(sprintf("Looking for imei: %s<br>\n", $this->ids['imei']));
     
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
        if ($gpsEntry === null)  // nie ma jeszcze ŻADNEGO wpisu
            return null;
        $this->lat = $gpsEntry['lat'];
        $this->lng = $gpsEntry['lng'];
        return $gpsEntry;
    }
    /**
     * Updates LATEST GPS Entry
     * Zabezpieczenie przed nadmiarowym zapisywaniem do bazy danych jest na poziomie GPSDBObject
     *
     * @param mixed $tower
     * 
     * @return [type]
     * 
     */
    private function updateGPS($tower, $distance, $realData = false)
    {
        if($this->lat == "0" OR $this->lat == 0)
        {
            $tower->log(sprintf("WARNING, 0 GPS detected"),true);
        }
        //error_log(sprintf("[%s][TID:%s] <br>\n", get_class($this).__FUNCTION__, $this->lat));
        $gpsupdate = new GPSDBObject();
        $gpsupdate->realData = $realData;   // flaga do uaktualniania last seen!
        $gpsupdate->lat = $this->lat;
        $gpsupdate->lng = $this->lng;
        $gpsupdate->refType = GPSDBObject::REFTYPE_TOWER;
        $gpsupdate->refID = $tower->id;
        $gpsupdate->distance = $distance;
        $gpsupdate->create();
        return $gpsupdate->id;
    }
    /**
     * Ta funkcja przetwarza dane, ale NIE uruchamia niczego innego
     * Warstwa danych przychodzących
     *
     * @return int kod błędu
     * 
     */
    public function runEngineInboundData($request)
    {
        // ładujemy dane
        $loadDataResult = $this->loadData($request);
        // jeśli ładowanie danych nie znajduje wieży
        if ($loadDataResult == GPSLogic::LOADDATA_TOWER_NOT_FOUND) {
            $towerWaitingRoom = new TowerWaitingRoom();
            $newTower = $towerWaitingRoom->addToWaitingRoom($this);
            //error_log(sprintf("New Tower is %s \n",$newTower));
            return GPSLogic::LOADDATA_TOWER_NOT_FOUND;
        }
        // jeśli załadowaliśmy dane, czas na włożenie ich do tablicy GPS
        // warunki
        // najpierw trzeba znaleźć wieżę, której dotyczą dane
        //error_log(sprintf("Looking for tower [%s]\n",$this->imei));
        $tower = new Tower();
        $towerID = $tower->findMe("imei", $this->imei)[0]['id'];
        if ("" == $towerID) {
            return GPSLogic::LOADDATA_TOWER_NOT_FOUND;
        }

        $tower->load((int)$towerID);
        //error_log(sprintf("Found tower [%s]\n",$tower->id));
        /*!SECTION
        $tower->print();
        */
        // Następnie trzeba zaktualizować dane w tabeli GPS
        $gpso = new GPSDBObject();
        $gpsoID = $gpso->returnLast(GPSDBObject::REFTYPE_TOWER, $tower->id);
        //error_log(sprintf("FOund this [%s]\n",json_encode($gpsoID)));
        if ("" == $gpsoID)       // jeśli nie ma jeszcze wpisu, trzeba stworzyć nowy
        {
            $this->updateGPS($tower, 0, true);
            //error_log(sprintf("Not Found GPS[%s]\n",$tower->id));
        } else {
            $gpso->load((int)$gpsoID['id']);
            //error_log(sprintf("Found! GPS[%s vs %s][%s]\n",$gpso->time_added,$gpso->time_updated, $gpso->returnCount()));
            $dist = $gpso->getDistance($tower, $this->lat, $this->lng);
            $this->updateGPS($tower, $dist, true);
        }
        $this->tower_id = $tower->id;
        return GPSLogic::GPSLOGIC_ENGINE2_SUCCESS;
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
                //error_log(sprintf("Problem z wieżą, nie była widziana od dawna (imei: %s)<br>\n", $tower->imei));
            };
            /**!SECTION
             * Druga rzecz do sprawdzenia, czy się ruszała o określony dystans
             */
            $distance = $this->checkMoved($tower, $towerSetting, $towerStatus);
            if (-1 == $distance) {
                //error_log(sprintf("Problem z wieżą, poruszyła się o więcej, niż zakładane (imei: %s)<br>\n", $tower->imei));
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
                //error_log(sprintf("Problem z wieżą, odjechała od punktu startowego, niż zakładane (imei: %s)<br>\n", $tower->imei));
            }
            /**
             * Zapisujemy ostatnią pozycję do historii, w zależności od towerSetting->keyFrame (!)
             * FIXME nie ma tutaj jeszcze keyFrame, zapisuję KAŻDY zgłoszony GPS, to rozwali bazę danych (100 wież * 600 zapisów na godzinę)
             */
            //error_log(sprintf("UPDATE GPS (imei: %s)<br>\n", $tower->imei));
            $this->updateGPS($tower, $distance, $realData = true);
            //$towerSetting->lastSeen  = $towerStatus->lastSeen;
            $towerSetting->update();
            // aktualizacja CURR bieżącej wieży
            $tower->currLat = $this->lat;
            $tower->currLng = $this->lng;
            //$tower->lastSeen = $towerSetting->lastSeen;
            $tower->update();
            //$towerStatus->lastSeen =  (new \DateTime())->Format("Y-m-d H:i:s");
            // zamiast powyższego...
            //$towerStatus->setLastSeen((new \DateTime())->Format("Y-m-d H:i:s"));
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
        //error_log(sprintf("%s:Brak danych z GPS w tabeli, tworzę!\n", __FUNCTION__));
        if ($lista == null) {
            // no tak, ale tu jeszcze nie ma danych z GPS, jeśli to jest pusty check
            $this->updateGPS($tower, 0, false);
        }
        //error_log(sprintf("[%s] gpsobj is %s <br>\n", __FILE__ . ">" . __FUNCTION__, json_encode($lista)));
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
    public function
    checkLastSeen($tower, $towerSetting, $towerStatus)
    {
        if (is_null($towerSetting)) return false;
        //printf("TOWER was last seen %s <br>\n", $towerSetting->lastSeen);
        //error_log(sprintf("[%s][TID:%s] [distanceMoved] %s <br>\n", __FUNCTION__, $tower->id,  $towerStatus->distanceMoved));
        //error_log(sprintf("[%s][TID:%s] [distanceFromStart] %s <br>\n", __FUNCTION__, $tower->id,  $towerStatus->distanceMovedFromStart));
        //error_log(sprintf("[%s][TID:%s] TOWER status has %s <br>\n", __FUNCTION__, $tower->id, $towerStatus->distanceMoved));
        //printf("TOWER status idle is %s <br>\n", $towerStatus->statusIdleTime);
        // sprawdź różnicę pomiędzy czasem lastseen a teraz:
        $now = new  DateTime();
        $then = $towerStatus->getLastSeen();
        //error_log(sprintf("[%s][TID:%s] THEN is %s <br>\n", __FUNCTION__, $tower->id, $then));
        $lastSeen = DateTime::createFromFormat("Y-m-d H:i:s", $then);
        //var_dump(\DateTime::getLastErrors());
        //error_log(sprintf("NOW vs THEN  is %s vs %s <br>\n", $lastSeen->Format("H:i:s"), $then));
        //error_log(sprintf("NOW vs THEN  is %s vs %s <br>\n", $now->getTimestamp(), $lastSeen->getTimestamp()));
        $diffInSeconds = $now->getTimestamp() - $lastSeen->getTimestamp();
        //error_log(sprintf("DIFF  is %s <br>\n", $diffInSeconds));
        $towerStatus->statusIdleTime = $diffInSeconds;
        if ($diffInSeconds > $towerSetting->idleTime) {
            printf("ALERT! %d is %s <br>\n", $towerSetting->idleTime, $diffInSeconds);
            //$towerStatus->flagLastSeen = true;
            $towerStatus->setFlag("flagLastSeen", "1", "[" . __FILE__ . "|" . __FUNCTION__ . "]");
            return true;    // true = mamy overtime
        }
        //error_log(sprintf("NOW vs THEN [%d] is %s vs %s <br>\n", $diffInSeconds, $now->Format("Y-m-d H:i:s"), $lastSeen->Format("Y-m-d H:i:s")));
        return false;
    }
    public function checkHomeBase2($tower, $gpso)
    {
        $project = new Project();
        $row = $tower->checkMeIn("tower_project");
        if (is_string($row)) {
            return self::GPSLOGIC_NO_PROJECT_FOUND;
        }
        $project->load((int)$row[0]['project_id']);
       // error_log(sprintf("Homebase lookup for %s, %s, ",$project->name, $tower->tower_nr));
        $homeBaseList = $project->checkMeIn("homebase_project");
        foreach ($homeBaseList as $key => $value) { // we take only FIRST homeBase!!!
            # odpal homeBase i sprawdź, czy wieża jest w home base
            $homeBase = new HomeBase((int)$value['homebase_id']);
            //error_log(sprintf("Homebase is for %s, %s, ",$homeBase->id, json_encode($value)));
            return 1000 * GPSDistance::getDistanceTwoObjects($homeBase, $gpso, __FUNCTION__);
            
        }
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
            $dist = 1000 * GPSDistance::getDistanceTwoObjects($homeBase, $this, __FUNCTION__);
            //error_log(sprintf("DISTANCE HB! %d is %s <br>\n", 0, $dist));
            /**!SECTION
             * Scenariusze
             * 1. Wieża jest w magazynie i była w magazynie
             */
            if ($dist < $towerSetting->distanceFromHomeBase && $towerStatus->flagEnteredHomeBase) {
                //error_log(sprintf("HB: In HB, not moved"));
            } else
            if ($dist < $towerSetting->distanceFromHomeBase && !$towerStatus->flagEnteredHomeBase) {
                $towerStatus->setFlag("flagEnteredHomeBase", true);
                // error_log(sprintf("HB: In HB, moved from outside"));
                // RAISE ALERT, weszliśmy do magazynu
            } else
            if ($dist > $towerSetting->distanceFromHomeBase && $towerStatus->flagEnteredHomeBase) {
                $towerStatus->setFlag("flagLeftHomeBase", true);
                //error_log(sprintf("HB: In HB, moved from outside"));
                // RAISE ALERT, weszliśmy do magazynu
            } else {
            }
            $towerStatus->distanceFromHomeBase = $dist;
            $towerStatus->update();
        }
    }
    // FIXME Dokończyć tę funkcję
    public function runEmptyEngine()
    {
        (new user_log("Starting runEmptyEngine", $this));
        //$this->user_log("Starting runAll", $this);
        $towerList = (new Tower())->list();
        //error_log(sprintf("Tower list is %s<br>\n", json_encode($towerList)));
        foreach ($towerList as $key => $value) {
            # code...
            //printf("Tower list  %s is %s<br>\n",$key, json_encode($value));
            $tower = new Tower((int)$value['id']);
            $this->runEngineForTower2($tower, $empty = true);
        }
    } // end of tunEmptyEngine()
    /**
     * Single run function for tower object
     * if $empty == true, to znaczy że to jest zwykłe sprawdzanie, a nie dane z routera!
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
    public function runEngineForTowerOBSOLETE($tower, $empty = true)
    {
        // OBSOLETE
        error_log(sprintf("%s: Tutaj?\n", __FUNCTION__));
        //$gpsEntry = $this->getLastGPS($tower);
        //$tower->print();
        $towerSetting = new TowerSetting(null, $tower);
        //$towerSetting->print();
        //return;
        if ($towerSetting->id == -1) {
            return;
        }    // no settings, this tower won't be checked
        $towerStatus = new TowerStatus($tower);
        $newData = $this->checkNewData($towerStatus, $tower);
        $this->checkLastSeen($tower, $towerSetting, $towerStatus);
        if ($newData === false && false === $empty) {
            // printf("Aktualizacja towerStatus");
            $towerStatus->update();
            return self::GPSLOGIC_NO_NEW_DATA;                          // no new data, don't bother...but we need lastSeen
        }
        //printf("Nowe date z GPS: %d\n", $newData);
        $distance = $this->checkMoved($tower, $towerSetting, $towerStatus);
        $this->checkHomeBase($tower, $towerSetting, $towerStatus);
        $distance = $this->checkMovedFromStart($tower, $towerSetting, $towerStatus);
        $towerStatus->update();
        $towerSetting->update();
        // uaktualnij wpisy w bazie GPS
        // ale po co, jeśli nie było potrzeby (bo nie ma danych z engine?)
        // FIXME cały empty engine needs reworking!
        if (!$empty) {
            $this->updateGPS($tower, $distance);
        }


    }
    private function checkNewData(TowerStatus $towerStatus, Tower $tower)
    {
        $gpsEntry = $this->getLastGPS($tower);
        if ($gpsEntry === null)
            return false;
        // error_log(sprintf("%s: date z GPS: %s vs %s\n", __FUNCTION__, $gpsEntry['time_updated'] , $towerStatus->lastSeen));
        // FIXME tu jest błąd w logice, bo wieża uaktualnia się w listen i tu już jest aktualna po czasie!
        if ($gpsEntry['time_added'] == $towerStatus->getLastSeen())
            return false;
        return true;
    }

    public function runEngineForTower2($tower)
    {
        //$gpsEntry = $this->getLastGPS($tower);
        $towerSetting = new TowerSetting(null, $tower);
        if ($towerSetting->id == -1) {
            return -1;  // no settings
        }    // no settings, this tower won't be checked
        $towerStatus = new TowerStatus($tower);
        if ($towerStatus->id == -1) {
            return -2;  // no staus
        }
        // w tym miejscu jest trójca Tower, Setting, Status, sprawdzamy najpierw, czy są nowe dane, czyli czy pozycja Status == pozycji w GPSData
        $gpso = new GPSDBObject();
        $gpsoID = $gpso->returnLast(GPSDBObject::REFTYPE_TOWER, $tower->id);
        if ("" == $gpsoID)       // jeśli nie ma jeszcze wpisu, trzeba stworzyć nowy
        {
            //error_log(sprintf("%s: Sprawdzam wieżę bez GPS! %s\n", __FUNCTION__, $tower->name));
            $gpso->time_updated = $towerStatus->time_added;
            // ustaw flagę że nie widać wieży!
            //$towerStatus->setFlag("flagLastSeen", true, __FUNCTION__);            
            //$towerStatus->log("Ustawiam flagę flagLastSeen", false);
            //
            $towerStatus->updateStatus($gpso, 18000);
            $towerSetting->log("Set idleTime to MAX");
            // ustawiam flagę nieczynna!
            

            $towerStatus->update();
            return -3; // no GPS recorded!
        }
        $gpso->load((int)$gpsoID['id']);
        //error_log(sprintf("Loading GPSO [%s][%s] \n", $gpso->id, $gpso->time_updated));
        // sprawdzamy, czy w towerStatus jest inny czas niż w gpso
        
        // Tu mamy komplet informacji, jedziemy ze sprawdzaniem
        
        /**!SECTION
         * Check last seen sprawdza różnicę pomiędzy TERAZ a ostatnim wpisem w GPS!
         */        

        $now = new  DateTime();
        $lastSeen = DateTime::createFromFormat("Y-m-d H:i:s", $gpso->time_updated);
        // FIXME to się wywali powyżej 1 dnia!
        $diffInSeconds = $now->getTimestamp() - $lastSeen->getTimestamp();
        if($diffInSeconds > 18000) {$diffInSeconds = 18000;}
      
        //$towerStatus->log(sprintf("Get Last Seen vs gpso time updated [%s][%s] = %s, TOWER: %s\n", $towerStatus->getLastSeen(), $gpso->time_updated, $diffInSeconds, $tower->name));
        
        
        /**!SECTION
         * Check Distance sprawdza TYLKO, jaki jest dystans od poprzedniego punktu
         */  
        $towerStatus->distanceMoved =  GPSDistance::calculateDistance4($gpso->lat, $gpso->lng, $tower->currLat, $tower->currLng);
        //$towerStatus->log(sprintf("Distance Moved = %d", $this->distanceMoved));
        //$towerStatus->log(sprintf("Get Distance = [%s][%s][%s][%s]%s\n", $tower->currLat, $tower->currLng, $gpso->lat, $gpso->lng,$towerStatus->distanceMoved));
        
        /**!SECTION
         * Check Distance From Start
         */  
        $towerStatus->distanceMovedFromStart =  GPSDistance::calculateDistance4($gpso->lat, $gpso->lng, $towerSetting->startLat, $towerSetting->startLng);

       // error_log(sprintf("Get Distance From The Start = [%s][%s][%s][%s]%s\n", $tower->currLat, $tower->currLng, $gpso->lat, $gpso->lng,$towerStatus->distanceMovedFromStart));
        
/**!SECTION
         * Check Distance From HomeBase
         */  
        $towerStatus->distanceFromHomeBase =  $this->checkHomeBase2($tower, $gpso);

       // error_log(sprintf("Get Distance From The HomeBase = [%s][%s][%s][%s]%s\n", $tower->currLat, $tower->currLng, $gpso->lat, $gpso->lng,$towerStatus->distanceFromHomeBase));
        // SECTION Ustawianie flag
        // Tutaj ustawiamy flagi!

        // REVIEW flaga czasowa
        //$towerStatus->log(sprintf("Setting flag %s\n", $towerSetting->idleTime));
        if($diffInSeconds >= $towerSetting->idleTime)
        {
            //$towerStatus->flagLastSeen
            $towerStatus->setFlag("flagLastSeen", true, __FUNCTION__);            
            //$towerStatus->log("Ustawiam flagę flagLastSeen");
        }
        if($towerStatus->distanceMoved >= $towerSetting->distanceMoved)
        {
            $towerStatus->setFlag("flagMoved", true, __FUNCTION__);            
            //$towerStatus->log("Ustawiam flagę flagMoved");
        }
        if($towerStatus->distanceMovedFromStart >= $towerSetting->distanceMovedFromStart)
        {
            $towerStatus->setFlag("flagMovedStart", true, __FUNCTION__);            
            //$towerStatus->log("Ustawiam flagę flagMovedStart");
        }
        //!SECTION
        

// SECTION Aktualizacja
        // koniec sprawdzania, aktualizacja!
        
        $towerStatus->updateStatus($gpso, $diffInSeconds);
        $towerStatus->update();

        // aktualizacja RUCHU wieży
        $tower->currLat = $gpso->lat;
        $tower->currLng = $gpso->lng;
        $tower->update();

        // uaktualnij wpisy w google sheets
        $gsl = new GoogleSheetLogic();
        $gsl->runTowerUpdate($tower);
        return 1;


        /*
        
        
        
        
        
        
        
        $newData = $this->checkNewData($towerStatus, $tower);
        $this->checkLastSeen($tower, $towerSetting, $towerStatus);
        if ($newData === false && false === $empty) {
            // printf("Aktualizacja towerStatus");
            $towerStatus->update();
            return self::GPSLOGIC_NO_NEW_DATA;                          // no new data, don't bother...but we need lastSeen
        }
        //printf("Nowe date z GPS: %d\n", $newData);
        $distance = $this->checkMoved($tower, $towerSetting, $towerStatus);
        $this->checkHomeBase($tower, $towerSetting, $towerStatus);
        $distance = $this->checkMovedFromStart($tower, $towerSetting, $towerStatus);
        $towerStatus->update();
        $towerSetting->update();
        // uaktualnij wpisy w bazie GPS
        // ale po co, jeśli nie było potrzeby (bo nie ma danych z engine?)
        // FIXME cały empty engine needs reworking!
        if (!$empty) {
            $this->updateGPS($tower, $distance);
        }
        */
    }
}
