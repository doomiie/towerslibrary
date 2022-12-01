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

class user_log extends \Database\DBObject
{
    protected $tableName = "user_log";
    public $message;

    public function __construct($message, $object)
    {
        parent::__construct();
        //error_log("Starting loggingn");
        $this->name = $object->getClassName();
        $this->message = $message;
        //printf("TUTAJ! %s, %s", $object->getClassName(), $message);
        return $this->create();
    }
}
