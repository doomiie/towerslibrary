<?php

/**
 * Organizacja:
 * 
 * Nadrzędna w stosunku do projektu, wieży i użytkownika
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

use GPS\GPSMaps;
use GoogleSheetTower\GoogleSheetTowerInterface;

class Tower extends \Database\DBObject
{
    protected $tableName = "tower";
    protected $parentClass = "project";
    // field names should be THE SAME as database names!
    public $lat;
    public $lng;
    public $currLat;
    public $currLng;

    /**
     * Startowa pozycja wieży, resetowalna
     *
     * @var float 12.8
     */
    public $startLat;
    /**
     * Startowa pozycja wieży, resetowalna
     *
     * @var float 12.8
     */
    public $startLng;
    public $tower_nr;
    public $imei;
    public $serial_nr;
    public $modem_type;

    /**
     * READONLY, ustawian w GPSLogic na podstawie danych z modemu
     *
     * @var [type]
     */
    //public $lastSeen;
    // też nie powinno być tower->lastSeen TYLKO w towerStatus!



    public function print()
    {
        parent::print();
        // TOWER powinna być w projekcie
        $row = $this->checkMeIn("tower_project");
        if (is_string($row)) {
            printf("PROBLEM: tower is not in project, error: %s  <br>\n", $row);
            //return -1;
        } else {
            printf("Tower project ID: %s  [%s]<br>\n", json_encode($row), $row[0]['project_id']);
            $projectID = (int)$row[0]['project_id'];
            $project = new Project((int)$projectID);
            printf("<hr> THIS Tower belongs to project: %s from %s<br>\n", $project->name, $projectID);
            $project->print();
        }
    }

    public function getTowerProject()
    {
        $row = $this->checkMeIn("tower_project");
        if (is_string($row)) {
            return null;
        } else {
            $projectID = (int)$row[0]['project_id'];
            $project = new Project((int)$projectID);
            return $project;
        }
    }

    /**
     * Ta funkcja na razie TYLKO uaktualnia excela, nie powinna aktualizować wieży
     *
     * @return int number of fields updated
     * 
     */
    public function updateSpreadsheet()
    {
        $project = $this->getTowerProject();
        $gsti =  new GoogleSheetTowerInterface($project->spreadsheetID);
        

    }
}
