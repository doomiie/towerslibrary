<?php

/**
 * Email - narzędzie do wysyłania emaili ze statusami
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

namespace MessagingSystem;

use Database\Alert;
use UserManagement\TowerStatus;


class Email extends EmailTransmission
{

    public const EMAIL_TEST = 0;
    const EMAIL_STATUS = 1;
    const EMAIL_TOWER_DOWN = 2;
    const EMAIL_TOWER_MOVED = 4;
    const EMAIL_TOWER_FROM_START = 8;

    public function install()
    {
        switch ($this->emailType) {
            case Email::EMAIL_TEST:
                $alert = new Alert();                
                return $alert->createAlertTest($this->pattern, Alert::ALERT_EMAIL_TEST, $this->id);
                break;
            case Email::EMAIL_STATUS:
                $project = $this->getParent();
                $alert = new Alert();                
                return $alert->createAlertEmail($this, Alert::ALERT_EMAIL_STATUS);
                break;
            
            default:
                # code...
                break;
        }

    }

    // TODO
    // Dodawanie alertów do maili, zwłaszcza test i status
    public function sendEmailTest()
    {
        $temp[] = "To jest email testowy\r\n";
        $temp[] = "W jego treści niewiele się dzieje\r\n";

        $this->subject = "Email testowy " . $this->name;

        parent::sendEmail($this->toAddress, $this->subject, Array($temp));

    }

    /**
     * Główna funkcja rozdzielająca emaile
     *
     * @param $toAddress =''
     * @param mixed $subject=''
     * @param  Array
     * 
     * @return [type]
     * 
     */
    public function sendEmail($toAddress ='', $subject='' , Array $body =null)
    {
       
        switch ($this->emailType) {
            case Email::EMAIL_TEST:
                $this->sendEmailTest();
                break;            
            case Email::EMAIL_STATUS:
                parent::sendEmail($this->toAddress, $subject, $body);
                break;
            
            default:
                # code...
                break;
        }
        return;
        $this->log("Sendinf Email!");
        $project = $this->getParent("email_project");
        //printf("Getting parent: %s <br>", $this->id);
        //$project->print();
        $towerList = $project->getAllTowers();

       // $this->print();
        //$project->print();

        $temp = null;
        foreach ($towerList as $key => $value) {
            # code...
            $towerStatus = new TowerStatus($value);
  
            $temp[] .= "-------------------------------------------------------------------\r\n";
            $temp2 = $towerStatus->printStatus($value);
            $temp = array_merge($temp, $temp2);
        }

        parent::sendEmail($this->toAddress, $this->subject, Array($temp));
    }
}
