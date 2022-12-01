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

class HomeBase extends \Database\DBObject
{
    protected $tableName = "homebase";
    protected $parentClass = "project";
    // field names should be THE SAME as database names!
    public $lat;
    public $lng;
    public $adres;

    /**
     * Domyślne, jeśli wieża przechodzi do projektu, ten radius jest wpisywany (chyba że jest -1, to wtedy zostaje wieży radius)
     *
     * @var [type]
     */
    public $detectionRadius = 100;
    
    
    public function getGoogleMapsLink($lat = "lat", $lng = "lng", $title="")
    {
        return (new GPSMaps)->getGoogleMapsLink($this->lat, $this->lng, $this->name);
    }

    public function print()
    {
        parent::print();
        // Project powinna być w organizacji
        $row = $this->checkMeIn("homebase_project");
        if(is_string($row))
        {
            printf("PROBLEM: homebase is not in project, error: %s  <br>\n", $row);
        return -1;
        }
        $projectID = (int)$row[0]['project_id'];
        printf("Homebase project ID: %s  [%s]<br>\n", json_encode($row), $row[0]['project_id']);
        $project = new Organization((int)$projectID);
        printf("<hr> THIS homebase belongs to project: %s from %s<br>\n", $project->name, $projectID);
        $project->print();
    }

}

