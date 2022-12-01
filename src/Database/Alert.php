<?php
/**
 * DB Alert engine
 
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

namespace Database;
use UserManagement\TowerSetting;

use MessagingSystem\Email;

/**
 * Ta klasa zajmuje się ustalaniem alertów
 * Typy alertów:
 * - uaktualnienie Google Sheet
 * - wysłanie emaila
 * - wysłanie SMS
 * - cokolwiek
 * Ideą jest niereagowanie od razu (np na ustawienie flagi), tylko na ustawienie alertu w odpowiedni sposób
 */
class Alert extends \Database\DBObject
{
    protected $tableName = "alert";
    //protected $childrenClassArray = array('project');

    protected $timeToRun;
    protected $alertType;
    protected $refID;       // ID wieży do odpalenia!
    public $message;

    const ALERT_TEST = 0;
    const ALERT_GSHEET = 1;
    const ALERT_EMAIL = 2;
    const ALERT_SMS = 4;


  

    public function createAlert(\UserManagement\Tower $tower, $alertType )
    {
        $towerS = new TowerSetting(null, $tower);
        
        $now = strtotime(date('H:i')) + $towerS->gracePeriod;
        $now = date('H:i', $now);
        $this->timeToRun = date('H:i',strtotime("+ $towerS->gracePeriod seconds"));
        $this->name = $towerS->name;
        $this->refID = $tower->id;
        $this->alertType = $alertType;   
        parent::create();
    }


    public function run()
    {
        if(!$this->active) { return -1;}
        $now = date("H:i");
        if($this->timeToRun > $now)
        {
            //$this->log(sprintf("WRONG TIME ALERT %s, message is %s, uruchamiany o %s", $this->id, $this->message, $this->timeToRun));
            //echo sprintf("TOO SOON Running alert %s, timeToRun: %s <>  %s :now ", $this->id, $this->timeToRun, $now);
            return -2;
        }
        if($this->timeToRun <= $now)
        {
            //$this->log(sprintf("LATE TIME ALERT %s, message is %s, uruchamiany o %s", $this->id, $this->message, $this->timeToRun));
            //echo sprintf("LATE OR OK Running alert %s, timeToRun: %s <>  %s :now ", $this->id, $this->timeToRun, $now);
        }
        switch ($this->alertType) {
            case Alert::ALERT_TEST:
                $this->user_log(sprintf("ALERT %s, message is %s, uruchamiany o %s", $this->id, $this->message, $this->timeToRun));
                $this->setFlag("active", 0);
                break;
            case Alert::ALERT_EMAIL:
                $this->runAlertEmail();
                $this->setFlag("active", 0);
                break;
            default:
              $this->user_log(sprintf("ALERT %s, message is %s, uruchamiany o %s, DEFAULT!", $this->id, $this->message, $this->timeToRun));
                $this->setFlag("active", 0);
            # code...
                break;
        }
      $this->update();
    }
    /**
     * Overload jako ciekawostka, żeby poprawnie pokazywać typ alertu
     *
     * @return [type]
     * 
     */
    public function print()
    {
        parent::print();
        //printf("FIELD: alertType, VALUE: [%s]<br>\n",  $this->getConstantName($this, $this->alertType));
    }

    public function runAlertEmail()
    {
        $tower = new \UserManagement\Tower((int)$this->refID);
        $towerS = new TowerSetting(null, $tower);
        $email = new Email();
        return $email->sendEmail("jerzy@zientkowski.pl","Test run Alert Email", Array('nic tu nie ma','ciekawego'));
        
    }
   
}