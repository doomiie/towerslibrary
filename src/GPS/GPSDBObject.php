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
    const REFTYPE_GENERIC = 0;
    const REFTYPE_TOWER = 1;
    const REFTYPE_HOMEBASE = 2;

    public $refID;

    public function returnCount($refType, $refID)
    {
        $sql = "select count(*) as ile from $this->tableName where refType = $refType and refID = $refID;";
        $row = $this->dbHandler->getRowSql($sql);
        //printf("COUNT %s", json_encode(($row)));
        return $row[0]['ile'];
    }

    /**
     * Funkcja zwraca OSTATNI wpis dotyczący danego typu
     *
     * @param mixed $refType
     * @param mixed $refID
     * 
     * @return [type]
     * 
     */
    public  function returnLast($refType, $refID)
    {
        $sql = "select * from $this->tableName where refType = $refType and refID = $refID order by time_added desc limit 1;";
        $row = $this->dbHandler->getRowSql($sql);
        //printf("COUNT %s", json_encode(($row)));
        return $row[0];

    }

    
}
