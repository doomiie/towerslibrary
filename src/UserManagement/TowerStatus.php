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
    public $lastSeen = null;
    
    /**
     * id wieży, której dotyczy setting
     *
     * @var [type]
     */
    public $tower_id;

    /**!SECTION
     * Flagi, onaczające status w przetwarzaniu
     */
    public $flagLastSeen  = false; // nie było jej widać
    public $flagMoved  = false; // ruszyła się
    public $flagMovedStart = false; // ruszyła się z pozycji startowej o treshold
    public $flagMovedHomeBase = false;  // 
    public $flagEnteredHomeBase = false;    // weszła do homebase
    public $flagLeftHomeBase = false;       // wyszła z homebase


    public function __construct($tower)
    {
        parent::__construct(null); 
        if(is_int($tower))  // ładowanie z konkretnego id, bo nie mam dostępu do obiektu
        {
            //error_log(sprintf("TOWER STATUS: %d ,<br>\n",$tower));
            $this->id = $tower;
            return $this->load((int)$this->id);
        }
        else
        {
            //error_log(sprintf("TOWER STATUS: not int, object %s ,<br>\n",json_encode($tower)));
            
        }
          
        $id = $this->findMe("tower_id", $tower->id); 
        $this->tower_id = $tower->id;
        //error_log(sprintf("FINDME: %d SQL %s,<br>\n",count($id),json_encode($id[0]['id'])));
        if($id == DBObject::FINDME_NOT_FOUND)
        {
            $this->tower_id = $tower->id;
            $this->lastSeen = $tower->lastSeen;
            $this->lat = $tower->lat;
            $this->lng = $tower->lng;
            $this->create();
        }
        else
        {
            $this->id = $id[0]['id'];
            $this->load((int)$this->id);
            //$this->update();
        }
    }

    // scenario consts
    const TS_HOMEBASE_ENTERED = 1;
    const TS_HOMEBASE_LEFT = 2;
    const TS_HOMEBASE_SPECIAL = 4;
}
