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
use UserManagement\Project;

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
    const ALERT_EMAIL_STATUS = 8;
    const ALERT_EMAIL_TEST = 16;


    public function createAlertEmail(\MessagingSystem\Email $email, $alertType)
    {
        $this->timeToRun = $email->pattern;
        $this->name = $email->name;
        $this->refID = $email->id;
        $this->alertType = $alertType;
        parent::create();
        return $this->id;
    }

    /**
     * Alert testowy, bez wieży i bez niczego ;)
     *
     * @param mixed $timeToRun
     * @param mixed 
     * 
     * @return [type]
     * 
     */
    public function createAlertTest($timeToRun, $alertType, $refID)
    {
        $this->timeToRun = $timeToRun;
        $this->name = "Alert testowy " . uniqid();
        $this->refID = $refID;
        $this->alertType = $alertType;
        return parent::create();
        
    }
    /**
     * [Description for createAlertStatus]
     *
     * @param \UserManagement\Project $project - 
     * @param mixed $alertType
     * @param mixed $refID
     * 
     * @return [type]
     * 
     */
    public function createAlertStatus($timeToRun, $alertType, $refID)
    {
        $project = new Project((int)$refID);
        $this->timeToRun = $timeToRun;
        $this->name = "Status dla projektu " . $project->name;
        $this->refID = $refID;
        $this->alertType = $alertType;
        return parent::create();
        

    }
    
    public function createAlert(\UserManagement\Tower $tower, $alertType )
    {
        $towerS = new TowerSetting(null, $tower);
        
        $now = strtotime(date('H:i')) + $towerS->gracePeriod;
        $now = date('H:i', $now);
        $this->timeToRun = date('H:i',strtotime("+ $towerS->gracePeriod seconds"));
        $this->name = $towerS->name;
        $this->refID = $tower->id;
        $this->alertType = $alertType;   
        return parent::create();
    }


    public function run(bool $force = false)
    {
        if(!$this->active) { return -1;}
        $now = date("H:i");
        $then = date("H:i", strtotime($this->timeToRun));
        if($then != $now && !$force)    // alerty za późno albo za wcześnie
        {
            //$this->log(sprintf("WRONG TIME ALERT %s, message is %s, uruchamiany o %s", $this->id, $this->message, $this->timeToRun));
            //echo sprintf("TOO SOON Running alert %s, timeToRun: %s <>  %s :now \r\n", $this->id, $then, $now);
            return -2;
        }
        //echo sprintf("Running alert %s, timeToRun: %s <>  %s :now \r\n", $this->id, $then, $now);
        
        switch ($this->alertType) {
            case Alert::ALERT_TEST:
                $this->user_log(sprintf("ALERT %s, message is %s, uruchamiany o %s", $this->id, $this->message, $this->timeToRun));
                $this->setFlag("active", 0);
                break;
                case Alert::ALERT_EMAIL:
                $this->user_log(sprintf("ALERT EMAIL %s, message is %s, uruchamiany o %s", $this->id, $this->message, $this->timeToRun));
                $this->runAlertEmail();
                $this->setFlag("active", 0);
                break;
                case Alert::ALERT_EMAIL_STATUS:
                    $this->user_log(sprintf("ALERT EMAIL STATUS %s, message is %s, uruchamiany o %s", $this->id, $this->message, $this->timeToRun));
                $this->runAlertEmailStatus($force);   // special kind, no deactivation!
                //$this->setFlag("active", 0);
                break; 
            case Alert::ALERT_EMAIL_TEST:
                $this->runAlertEmailTest();   // special kind, no deactivation!
                //$this->setFlag("active", 0);
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
    public function runAlertEmailTest()
    {
       
        $email = new Email((int)$this->refID);
        return $email->sendEmail();
        
    }    
    /**
     * Ta funkcja powinna wysyłać EmailStatus
     *  - dla każdego projektu ze zdefiniowanym mailem
     *
     * @return [type]
     * 
     */
    //REVIEW - Temporary solution!
    public function runAlertEmailStatus(bool $force = false)
    {
        // w ID mamy refID emaila
        //echo $this->refID;
        $email = new Email((int)$this->refID);
        // w email_project mamy partna
        $project = $email->getParent();
        //$project->print();
        $temp[] = sprintf("Raport automatyczny %s, wygenerowany o %s dla %s\n", $this->id, $this->name, $this->timeToRun, $email->toAddress);
        $temp[] = $force? sprintf("Raport wysłany z flagą force (ręcznie?)\n") : '';
        $counter = 0;
        $towerList = $project->getChildren();
        foreach ($towerList as $key => $value) {
            if($key != "tower") { continue;}
            foreach ($value as $key1 => $value1) {
                $towerStatus = new \UserManagement\TowerStatus($value1);
                $testArray =  $towerStatus->printStatusAlert($value1);
                error_log(sprintf("KEY: %s, %s<br><hr>", $key, $testArray));
                if(null === $testArray) { continue; }
                $temp[] = $testArray;
                $counter++;
            }
           // $value[0]->print();
        }
        $txt = json_encode($temp,JSON_UNESCAPED_UNICODE);
        //printf("ENCODED! %s, <br><hr>", $txt);
        $this->user_log(mb_strimwidth($txt,0,200,"(...)") . "COUNTER: $counter");    // zawsze loguj alert
        if($counter > 0 || $force) { $email->sendEmail('','',$temp);} // wysyłaj TYLKO, jeśli jest coś do wysłania
        //return $email->sendEmail("jerzy@zientkowski.pl","Test run Alert Email", $temp);
        return $counter;
        
    }
   
}