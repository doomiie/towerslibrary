<?php
/**
 * Tower Setting
 * Single tower settings, that affect GPSLogic module
 * To be loaded against processing engine
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

use DateTime;

class TowerSetting extends \Database\DBObject
{
    protected $tableName = "tower_setting";
    // field names should be THE SAME as database names!

    /**
     * Czas, w sekundach, po którym wieża ma dostać status "zgubiona"
     *
     * @var int 
     */
    public $idleTime;

    /**
     * Dystans ruchu od POPRZEDNIEJ pozycji
     *
     * @var [type]
     */
    public $distanceMoved;
    
    /**
     * odległość od punktu startowego, po jakiej jest generowany alarm
     *
     * @var [type]
     */
    public $distanceMovedFromStart;

    /**
     * Dystans od HomeBase, po którym włączamy alarm
     *
     * @var [type]
     */
    public $distanceFromHomeBase;

    /**
     * Timestamp, kiedy ostatnio była widziana
     *
     * @var [type]
     */
    //public $lastSeen;
    // tego nie powinno być w tower setting!
    
    /**
     * id wieży, której dotyczy setting
     *
     * @var [type]
     */
    public $tower_id;

    // Patrz opisy pól w wieży
    public $startLat;
    public $startLng;

    public $gracePeriod;    // 15 minut

    public function __construct($id = null, $tower = null)
    {
        parent::__construct($id);        
        if($this->id == -1 AND $tower === null) // pusta wieża i niezaładowany obiekt
        {
        $this->id = -1;
        return;
        }
        if($this->id != -1) // załadowane z bazy danych, działa
        {
        //    $this->id = -2;
            return;
        }
        
        //error_log(sprintf("Looking for tower id: %s<br>\n",$tower->id));
        $id = $this->findMe("tower_id", $tower->id);    
        //error_log(sprintf("TowerSetting found id: %s<br>\n",json_encode($id)));
        $this->load((int)$id[0]['id']);
        $tower->startLat = $this->startLat;
        $tower->startLng = $this->startLng;
        $tower->update();
        

    }
}
