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

namespace UserManagement;



class Email extends \Database\DBObject
{
    protected $tableName = "email";
    protected $parentClass = "project";
    public $toAddress = "jerzy@zientkowski.pl";  // do kogo wysyłać
    public $subject = " mail testowy";
    /**
     * Wzór godzinowy, jak wysyłać emaile
     * Wzięte z crona
     *
     * @var string
     */
    public $pattern = " mail testowy";

    const EMAIL_TYPE_STATUS = 1;
    const EMAIL_TYPE_TEST = 2;
    const EMAIL_TYPE_TOWER = 3; // mail wysyłany, kiedy podnosimy flagę dla wieży!
    protected $emailType = self::EMAIL_TYPE_STATUS;

    public function setFlag($flagName, $flagValue, $from = "setFlag")
    {
        
        parent::setFlag($flagName, $flagValue, $from);
        // aktywowanie maili powoduje instalację lub deinstalację!
        if($flagName == 'active')
        {
            switch ($flagValue) {
                case 1:
                    # code...
                    return $this->install();
                    break;
                case 0:
                    return $this->remove();
                    break;
                
                default:
                    return -1;
                    break;
            }

        }
    }

    // FIXME tu jest zrąbane, bo nie instaluje!
    protected function install()
    {
        $bashCmd = "/var/www/gps/cron/script.sh add $this->id \"$this->pattern\"";
        error_log($bashCmd);

        $returnVal =  shell_exec($bashCmd); 
        //$this->log(json_encode($output) . " > " . $retval);
        return $returnVal;

      
    }

    protected function remove()
    {

        $bashCmd = "/var/www/gps/cron/script.sh del $this->id \"$this->pattern\"";
        error_log($bashCmd);

        $returnVal =  shell_exec($bashCmd); 
        //$this->log(json_encode($output) . " > " . $retval);
        return $returnVal;
       
    }
 

    public function sendEmail()
    {
        $this->sendEmailStatus();
        // TODO
        // SEnd Email Alert (tylko, jeśli flaga się zmieniła)
    }
    
    public function sendEmailStatus()
    {
        $this->log("Sendinf Email!");
        $project = $this->getParent();
        //printf("Getting parent: %s <br>", $this->id);
        $towerList = $project->getAllTowers();

       // $this->print();
        //$project->print();

        $_ZAP_ARRAY['to'] = $this->toAddress;
        $_ZAP_ARRAY['subject'] = sprintf("Projekt [%s], raport dla %s, %s", $project->name, date("Y-d-n "), $this->subject);
        $temp = null;
        foreach ($towerList as $key => $value) {
            # code...
            $towerStatus = new TowerStatus($value);
  
            $temp[] .= "-------------------------------------------------------------------\r\n";
            $temp2 = $towerStatus->printStatus($value);
            $temp = array_merge($temp, $temp2);
        }
        $_ZAP_ARRAY['raport'] = json_encode($temp);


        // stuff it into a query
        $_ZAP_ARRAY = http_build_query($_ZAP_ARRAY);

        // get my zap URL
        $ZAPIER_HOOK_URL = "https://hooks.zapier.com/hooks/catch/2289511/bpwyj44/";

        // curl my data into the zap
        $ch = curl_init($ZAPIER_HOOK_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $_ZAP_ARRAY);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        return $response;
    }

    public function sendEmailGeneric($toAddress, $subject, Array $body)
    {
        $_ZAP_ARRAY['to'] = $toAddress;
        $_ZAP_ARRAY['subject'] = $subject;
        $_ZAP_ARRAY['raport'] = json_encode($body);
        // stuff it into a query
        $_ZAP_ARRAY = http_build_query($_ZAP_ARRAY);

        // get my zap URL
        $ZAPIER_HOOK_URL = "https://hooks.zapier.com/hooks/catch/2289511/bpwyj44/";

        // curl my data into the zap
        $ch = curl_init($ZAPIER_HOOK_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $_ZAP_ARRAY);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        return $response;
    }
}
