<?php

/**
 * Google Sheet Interface for towers:
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

namespace GoogleSheetTower;
use Exception;
//require_once 'vendor/autoload.php'; // Autoload files using Composer autoload

class GoogleSheetTowerInterface
{
    public $service;
    public $spreadsheetId;
    public $client;

    public $towerRowNumber = -1;        // numer znalezionej wieży
    public $towerArray;                 // array z wieżą
    public $allTowersArray;             // wszystkie wieże

    protected $homePath;

    public function __construct($spreadsheetId = "")
    {
        $this->client = new \Google_Client();
        $this->client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $this->client->setAccessType('offline');
        // credentials.json is the key file we downloaded while setting up our Google Sheets API
        if(empty($_SERVER['DOCUMENT_ROOT']))
        {$this->homePath = "/var/www/gps";        }
        else
        {            $this->homePath = $_SERVER['DOCUMENT_ROOT'];        }
        $path = $this->homePath .'/logowaniesandbox-b6f0bd572f18.json';
        
        $this->client->setAuthConfig($path);

        $this->service = new \Google_Service_Sheets($this->client);

        $this->spreadsheetId = $spreadsheetId;
    }
    public function getTowersFromSheet($forceRefresh = false)
    {
        if($this->allTowersArray !== null && !$forceRefresh)
        {
            return $this->allTowersArray;
        }
        $range = 'Arkusz1';
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        $rows = $response->getValues();
        // Remove the first one that contains headers
        $headers = array_shift($rows);
        // Combine the headers with each following row
        $array = [];
        foreach ($rows as $row) {
            $this->allTowersArray[] = array_combine($headers, $row);
        } 
        return $this->allTowersArray;
    } 

    public function findTowerRowNumber(\UserManagement\Tower $tower)
    {
        $array = $this->getTowersFromSheet();
        if(null == $array)
        return -1;
        $result = array_search($tower->tower_nr, array_column($array, 'Numer wiezy'));
        if($result === false)
        return -1;
        $this->towerRowNumber = $result;
        return $this->towerRowNumber;

    }

    public function getTowerRow(\UserManagement\Tower $tower)
    {
        $array = $this->getTowersFromSheet();
        if($this->towerRowNumber == -1)
        {
        $this->towerRowNumber = array_search($tower->tower_nr, array_column($array, 'Numer wiezy'));
        error_log(sprintf("[%s] Szukam %s:<br>\n", __FUNCTION__ , $this->towerRowNumber));
        }
        $this->towerArray = $array[$this->towerRowNumber];
        return $this->towerArray;

    }
    public function getTowerParam(\UserManagement\Tower $tower, string $paramName)
    {
       //printf("[%s] Szukam %s:<br>\n", __FUNCTION__ , $paramName);
        //$arraySearch = $this->getTowerRow($tower);
        //printf("[%s] ARRAY %s:<br>\n", __FUNCTION__ , json_encode($this->towerArray));
        $found = $this->towerArray[$paramName];
        return $found;
        
    }
    
    public function setTowerParam(\UserManagement\Tower $tower, string $paramName, $value)
    {
        //$this->towerArray = $this->getTowerRow($tower);
        //printf("[%s] UPDATE %s > %s:<br>\n", __FUNCTION__ , $paramName, $value);
        $this->towerArray[$paramName] = $value;
        //printf("[%s] ARRAY %s:<br>\n", __FUNCTION__ , json_encode($this->towerArray));
        //$this->updateTowerRow($this->findTowerRowNumber($tower), array_values($this->towerArray));
        
    }
    
    public function updateTowerRow()
    {
       // printf("[%s] ARRAY %s, row number %s:<br>\n", __FUNCTION__ , json_encode($this->towerArray), $this->towerRowNumber);
        
        $rows = [array_values($this->towerArray)];
        //$this->log(json_encode($rows));
        $valueRange = new \Google_Service_Sheets_ValueRange();
        $valueRange->setValues($rows);
        //printf("[%s] Tower UPDATE: %s:<br>\n", __FUNCTION__ , json_encode($rows));
        $range = 'Arkusz1!A'. ($this->towerRowNumber + 2); // where the replacement will start, array starts at 0, we need to add 2 !
        $options = ['valueInputOption' => 'USER_ENTERED'];
        $this->service->spreadsheets_values->update($this->spreadsheetId, $range, $valueRange, $options);
    }

    public function addTowerRow()
    {
         
        $rows = [array_values($this->towerArray)];
        $valueRange = new \Google_Service_Sheets_ValueRange();
        $valueRange->setValues($rows);
        $range = 'Arkusz1!A1';
        $options = ['valueInputOption' => 'USER_ENTERED'];
        $response = $this->service->spreadsheets_values->append($this->spreadsheetId, $range,$valueRange, $options);
        //printf("[%s] Tower ADD: %s:<br>\n", __FUNCTION__ , json_encode($rows));
        //update($this->spreadsheetId, $range, $valueRange, $options);
        return $response;
    }

    public function log($message, $trace = false)
    {
//        $debugBacktrace = json_encode(debug_backtrace());
        $home = $this->homePath;
        $fileName = sprintf("%s/log/%s.log",$home,date("Y-n-d"));
        if(!file_exists($fileName))
        {
            touch($fileName);
            chmod($fileName, 0777);
        }
        $text = sprintf("[%s][%s][%s] \r\n",date("G-i-s"), get_class($this), $this->spreadsheetId);
        error_log($text, 3, $fileName);
        error_log($message, 3, $fileName);
        if(!$trace) return;
        $e = new \Exception();
        $debugBacktrace = sprintf("%s\r\n",$e->getTraceAsString());
        error_log($debugBacktrace, 3, $fileName);
    }
    
}
