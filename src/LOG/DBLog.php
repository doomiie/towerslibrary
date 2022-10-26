<?php
/**
 * DBLog: logowanie eventów do bazy danych
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
namespace LOG;

class DBLog extends \Database\DBObject
{
    protected $tableName = "log";
    public $message;

    public function log($message, $name = "Name is not set")
    {
        //printf("describe is [%s]<br>\n", $this->message));
        $this->name = $name;
        $this->message = $message;
        echo $this->create();
    }
}
