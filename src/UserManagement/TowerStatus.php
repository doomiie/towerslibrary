<?php
/**
 * Tower Status
 * Single tower status, calculated based on tower signal or clock event
 
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
use DateTime;
use GoogleSheetTower\GoogleSheetTower;
use GPS\GPSDBObject;

class TowerStatus extends \Database\DBObject
{
    protected $tableName = "tower_status";
    
    // field names should be THE SAME as database names!

    /**
     * Czas, w sekundach, po którym wieża ma dostać status "zgubiona"
     *
     * @var int 
     */
    public $statusIdleTime = 0;

    /**
     * Dystans ruchu od POPRZEDNIEJ pozycji
     *
     * @var [type]
     */
    public $distanceMoved = 0;
    public $statusDistanceFromHomeLast = 0; // nie mam pojęcia, co to jest, zapomniałem idei
    
    /**
     * odległość od punktu startowego, po jakiej jest generowany alarm
     *
     * @var [type]
     */
    public $distanceMovedFromStart = 0;
    

    /**
     * Dystans od HomeBase, po którym włączamy alarm
     *
     * @var [type]
     */
    public $distanceFromHomeBase = 0;

    /**
     * Timestamp, kiedy ostatnio była widziana
     *
     * @var [type]
     */
    protected $lastSeen = null;
    public  $lastSeenUnsafeCopy = null;
    public function setLastSeen($value)
    { $this->lastSeen = $value;
      $this->lastSeenUnsafeCopy = $value;  }
    public function getLastSeen()
    {$this->lastSeenUnsafeCopy = $this->lastSeen; 
        return $this->lastSeen;}
    
    /**
     * id wieży, której dotyczy setting
     *
     * @var [type]
     */
    public $tower_id;

    /**
     * Liczba wpisów w DB
     *
     * @var [type]
     */
    public $gpsCount;

    /**!SECTION
     * Flagi, onaczające status w przetwarzaniu
     */
    public $flagLastSeen  = 0; // nie było jej widać
    public $flagMoved  = 0; // ruszyła się
    public $flagMovedStart = 0; // ruszyła się z pozycji startowej o treshold
    public $flagMovedHomeBase = 0;  // 
    public $flagEnteredHomeBase = 0;    // weszła do homebase
    public $flagLeftHomeBase = 0;       // wyszła z homebase


    public function __construct($tower)
    {
        parent::__construct(null); 
        
        if(is_int($tower))  // ładowanie z konkretnego id, bo nie mam dostępu do obiektu
        {
            //$this->log(sprintf("TOWER STATUS construct 1: %d ,<br>\n",$this->id));
            
            $this->id = $tower;
            $result =  $this->load((int)$this->id);
            // trzeba jeszcze uaktualnić last seen z bazy danych, zdaje się :)
            // nie uaktualniamy w nowym procesie!
            //$this->updateLastSeenFromDB(1);
            $this->getLastSeen();
            return $result;
        }
        else
        {
            //$this->log(sprintf("TOWER STATUS: not int, object %s ,<br>\n",json_encode($tower)));
            
            
        }
          
        $id = $this->findMe("tower_id", $tower->id); 
        $this->tower_id = $tower->id;
        //$this->log(sprintf("FINDME: %d, ID content: %s,<br>\n",count($id),json_encode($id)));
        if($id == DBObject::FINDME_NOT_FOUND)
        {
            //$this->log(sprintf("FINDME Not found, creating"));
            $this->tower_id = $tower->id;
            $this->setLastSeen((new \DateTime())->Format("Y-m-d H:i:s"));
            //$this->setLastSeen($tower->lastSeen);
            $this->lat = $tower->lat;
            $this->lng = $tower->lng;
            // trzeba jeszcze uaktualnić last seen z bazy danych, zdaje się :)
            $this->updateLastSeenFromDB(2);
            $this->create();
        }
        else
        {
            //$this->log(sprintf("FINDME found, loading"));
            $this->id = $id[0]['id'];
            $result = $this->load((int)$this->id);
            // trzeba jeszcze uaktualnić last seen z bazy danych, zdaje się :)
            $this->updateLastSeenFromDB(3);
            $this->getLastSeen();
            //$this->update();
            return $result;
        }
        
    }

    // scenario consts
    const TS_HOMEBASE_ENTERED = 1;
    const TS_HOMEBASE_LEFT = 2;
    const TS_HOMEBASE_SPECIAL = 4;

    protected function updateLastSeenFromDB($from=0)
    {
        return 0;// nie uaktlalniamy na razie, bo psuje się proces!
        $gps = new GPSDBObject();
        $row = $gps->returnLast(GPSDBObject::REFTYPE_TOWER, $this->tower_id);
        if($row === null)   // nie ma ŻADNEGO wpisu jeszcze, co teraz?
        {
            //error_log(sprintf("[%s][TID:%s][FROM=%d] PROBLEM: brak wpisu GPS %s <br>\n", __FUNCTION__, $this->tower_id, $from, $this->lastSeen));
            $this->setLastSeen((new \DateTime())->Format("Y-m-d H:i:s"));
            $this->gpsCount = 0;
            return;
        }
        //error_log(sprintf("TOWER status has %s for %d <br>\n", json_encode($row), $this->tower_id));
        $this->setLastSeen($row['time_updated']);
        $this->gpsCount = $row['counter'];
    }

    public function updateStatus(GPSDBObject $gpso, $diffInSeconds)
    {
        // to nie działa w statusach, tylko podczas pobierania danych z gpso
        $this->setLastSeen($gpso->time_updated);
        //!SECTION$this->lastSeenUnsafeCopy = $gpso->time_updated;// włączone w setLastSeen!
        $this->statusIdleTime = $diffInSeconds;
        $this->gpsCount = $gpso->returnCount();
    }

    /**
     * Override parenta, bo tower status musi jeszcze sprawdzać zmianę i wywoływać zmiany w google sheet
     *
     * @param mixed $flagName
     * @param mixed $flagValue
     * @param string $from
     * 
     * @return [type]
     * 
     */
    public function setFlag($flagName, $flagValue, $from = "setFlag", $forceRefresh = true)
    {
        if($this->$flagName != $flagValue) // tu mamy zmianę!
        {
        parent::setFlag($flagName, $flagValue, $from);// odpal parenta, a następnie zaktualizuj google sheet
        $tower = new Tower((int)$this->tower_id);   // tu jest tower
        $project = $tower->getTowerProject();       // tu jest project
        $gsti = new GoogleSheetTower($project->spreadsheetID);  // tu mamy spraedsheet
        if($forceRefresh)
        $gsti->updateTower($tower);

        }
    }

    public function printStatus($tower)
    {
        $arrayText[] = sprintf("Wieża [%s] %s, %s\r\n", $tower->id, $tower->name, $tower->tower_nr);
        $arrayText[] = sprintf("Status wieży [%s] %s\r\n", $this->id, $this->tower_id);
        $arrayText[] = sprintf("Ostatnio widziana o  %s\r\n", $this->lastSeenUnsafeCopy);
        if($this->flagLastSeen) {$arrayText[] = sprintf(">>> UWAGA! Wieża może być wyłączona \r\n");}
        if($this->flagMoved) {$arrayText[] = sprintf(">>> UWAGA! Wieża poruszona \r\n");}
        if($this->flagMovedStart) {$arrayText[] = sprintf(">>> UWAGA! Wieża zmieniła miejsce pobytu (od startu)\r\n");}

        return $arrayText;
    }

    public function printStatusAlert($tower)
    {
        if(!$this->flagLastSeen && !$this->flagMoved && !$this->flagMovedStart) return null;
        return $this->printStatus($tower);
    }

    public function resetAllFlags()
    {
        //$this->print();
        error_log("Setting all flags for " . $this->id);
        $this->setFlag("flagLastSeen", 0, __CLASS__ . "=>" . __FUNCTION__, false);
        $this->setFlag("flagMoved", 0, __CLASS__ . "=>" . __FUNCTION__, false);
        $this->setFlag("flagMovedStart", 0, __CLASS__ . "=>" . __FUNCTION__, false);
        $this->setFlag("flagMovedHomeBase", 0, __CLASS__ . "=>" . __FUNCTION__, false);
        $this->setFlag("flagEnteredHomeBase", 0, __CLASS__ . "=>" . __FUNCTION__, false);
        $this->setFlag("flagLeftHomeBase", 0, __CLASS__ . "=>" . __FUNCTION__); // forceRefresh = true, żeby odświeżyć excela

        // zmiana startu
        $tower = $this->getParent();
        $towerS = new TowerSetting(null, $tower);
        error_log(sprintf("Tower Settings START lat %s, lng %s\n", $towerS->startLat, $towerS->startLng));
        $towerS->startLat = $tower->currLat;
        $towerS->startLng = $tower->currLng;
        $towerS->update();
        $this->update();
        //$this->print();
        
    }

    public function getParent()
    {
        return new Tower((int)$this->tower_id);
    }
    
}
