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



class EmailTransmission extends \Database\DBObject
{
    protected $tableName = "email";    
    protected $parentClass = "project";
    public $toAddress;
    public $subject;
    public $pattern;   
    public $emailType;
    public $refID;  // np na ID projektu    
    /**
     * Wzór godzinowy, jak wysyłać emaile
     * Wzięte z crona
     *
     * @var string
     */

    public function sendEmail($toAddress, $subject, Array $body)
    {
        //printf("EMIAL: %s, %s, %s", $toAddress, $subject, json_encode($body));
        $_ZAP_ARRAY['to'] = $toAddress == ''? $this->toAddress:$toAddress;
        $_ZAP_ARRAY['subject'] = $subject == ''?$this->subject:$subject;
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
