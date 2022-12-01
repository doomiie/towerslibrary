<?php
/**
 * GPS Database object, to read and write GPS data
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
class GPSDBObject extends \Database\DBObject
{
    protected $tableName = "gpsdata";
    // field names should be THE SAME as database names!
    public $lat;
    public $lng;
    public $refType;
    const REFTYPE_EMPTY = -1;
    const REFTYPE_GENERIC = 0;
    const REFTYPE_TOWER = 1;
    const REFTYPE_HOMEBASE = 2;
    public $refID;
    public $distance = 0;   // for saving from GPSLogic
    public $realData = false;   // for saving time_updated
    /**
     * Licznik ticków z tą samą pozycją
     *
     * @var [type]
     */
    protected $counter = 0;
    public function returnCount($refType = "", $refID = "")
    {
        if($refType == "") $refType = $this->refType;
        if($refID == "") $refID = $this->refID;
        //$sql = "select count(*) as ile from $this->tableName where refType = $refType and refID = $refID;";
        $sql = "select sum(counter) as ile from $this->tableName where refType = $refType and refID = $refID;";
        $row = $this->dbHandler->getRowSql($sql);
        //printf("COUNT %s", json_encode(($row)));
        return $row[0]['ile'];
    }

    public function returnCounter()
    {
        return $this->counter;
    }
    /**
     * Funkcja zwraca OSTATNI wpis dotyczący danego typu
     * uwaga, wpis po time_added nie działa dla 
     *
     * @param mixed $refType
     * @param mixed $refID
     * 
     * @return [type]
     * 
     */
    public  function returnLast($refType, $refID)
    {
        $sql = "select * from $this->tableName where refType = $refType and refID = $refID order by time_updated desc limit 1;";
        $row = $this->dbHandler->getRowSql($sql);
        if ($row === null)
            return null;
        //printf("COUNT %s", json_encode(($row)));
        if(empty($row))
        return null;
        return $row[0];
    }
    /**
     * Overloaded create. Checks if the tick exists and adds counter to it, if the data matches
     *
     * @return [type]
     * 
     */
    public function create()
    {
        //error_log(__CLASS__ . " create");
        $row = $this->returnLast($this->refType, $this->refID);
        if (!is_null($row))  // mamy rezultat dla type i id
        {
            //error_log(sprintf("COMPARE: %s vs %s, %s vs %s to create %s\n", $row['lat'], $this->lat, $row['lng'], $this->lng, json_encode($row)));
            // trzeba porównać dane
            //if($row['lat'] == $this->lat AND $row['lng'] == $this->lng) // to samo, nie ma sensu tworzyć NOWEJ instancji
            //if(strncmp($row['lat'] ,$this->lat, 6) AND strncmp($row['lng'], $this->lng,6)) // to samo, nie ma sensu tworzyć NOWEJ instancji
            // REVIEW jeśli przesunięcie jest mniejsze od 1m, nie zapisuję zmiany. Uwaga na przesunięcie od punktu startowego etc.
            if ($this->distance < 1) {
                //error_log(sprintf("NO NEED to create %s\n", json_encode($row)));
                $this->load((int)$row['id']);
                if ($this->realData == true) {
                    $this->time_updated = (new \DateTime())->Format("Y-m-d H:i:s");
                    //error_log(sprintf("Real data: %s, %s\n", $this->realData, $this->time_updated));
                }
                $this->counter++;
                $this->update();
                return;
            }
        }
        //error_log(sprintf("NEED to create, distance is %s\n", $this->distance));
        parent::create();
    }

    public function getDistance($tower, $lat, $lng)
    {
        $gpso = new GPSDBObject();
        $gpsoID = $gpso->returnLast(GPSDBObject::REFTYPE_TOWER, $tower->id);
        
        if("" == $gpsoID)       // jeśli nie ma jeszcze wpisu, 
        return 0;
        if ("" == $gpsoID)       // jeśli nie ma jeszcze wpisu, trzeba stworzyć nowy
        {
            return -3; // no GPS recorded!
        }
        $gpso->load((int)$gpsoID['id']);
        $dist =  GPSDistance::calculateDistance4($gpso->lat, $gpso->lng, $lat, $lng);
        //error_log(sprintf("Get Distance = [%s][%s][%s][%s]%s\n", $tower->lat, $tower->lng, $this->lat, $this->lng,$dist));
        return $dist;
    }
}
