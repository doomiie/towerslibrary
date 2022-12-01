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

/**
 * Ta klasa zajmuje się ustalaniem alertów
 * Typy alertów:
 * - uaktualnienie Google Sheet
 * - wysłanie emaila
 * - wysłanie SMS
 * - cokolwiek
 * Ideą jest niereagowanie od razu (np na ustawienie flagi), tylko na ustawienie alertu w odpowiedni sposób
 */
class AlertEngine extends \Database\DBObject
{
    public function runAll()
    {
        $this->user_log("Starting runAll", $this);
        $alertList = (new Alert())->objectList();
        $result = 0;
        foreach ($alertList as $key => $value) {
            //printf("Running alert %s %s<br>",$key, $value->name);
            $result += $this->runAlert($value);
            //printf("DONE running alert %s<br>", $value->name);
        }
        return $result;  
    }   

    public function runAlert(\Database\Alert $alert)
    {
        $alert->run();
        return 1;
    }
}