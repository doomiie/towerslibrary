<?php

/**
 * Organizacja:
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

namespace Database;

use LOG\DBLog;

class DBObject
{
    protected $tableName = "";
    public $id;
    public $name;
    public $active;
    public $time_added;

    public $dbHandler;


    public function __construct($id = null)
    {
        $this->dbHandler = new DBHandler();
        $this->id = -1;
        if(!is_null($id))
        {
        $this->load($id);
        return;
        }
        
    }

    protected function obj2array ( &$instance ) {
        $clone = (array) $instance;
        $rtn = array ();
        $rtn['___SOURCE_KEYS_'] = $clone;
    
        while ( list ($key, $value) = each($clone) ) {
            $aux = explode ("\0", $key);
            $newkey = $aux[count($aux)-1];
            $rtn[$newkey] = &$rtn['___SOURCE_KEYS_'][$key];
        }
    
        return $rtn;
    }

    /**
     * Loads ALL fields that are in the database !
     *
     * @param int $id ID of field
     * @param string $table table to be mentioned
     * 
     * @return [type] fields affected
     * 
     */
    public function load(  $id)
    {
        
        $table = $this->tableName;
        if(is_int($id))
        {
        $sql = "select * from $table where id = '$id'";
        }
        if(is_string($id))
        {
            $sql = "select * from $table where name = '$id'";
        }
        // what if there's no data or error?
        $row = $this->dbHandler->getRowSql($sql);
        if(empty($row)) {return 0;}
        $row = $row[0];
        //echo "Loading" . json_encode($row);
        
        $fieldArray = $this->obj2array($this);
        $fieldCounter = 0;
        foreach ((array)$fieldArray as $key => $val) {
            if(is_object($val)) continue;
            $key = htmlspecialchars(trim($key));
            if(isset($row[$key]))
            {
                //printf("setting%sto %s [%s]<br>\n", $key, $row[$key], "");//$this->{$key});
                $this->{$key} = $row[$key];
                $fieldCounter++;
            }
            
            
        }
        return $fieldCounter;
    }

    public function update()
    {
        // prepare SQL first
        // get definition
        
        error_log(sprintf("UPDATE FOR %s<br>\n", get_class($this)));
        $sql = "DESCRIBE $this->tableName";
        $row = $this->dbHandler->getRowSql($sql);
        foreach ($row as $key => $value) {
            # code...
            //error_log(sprintf("key [%s] is [%s]<br>\n", $key, json_encode($value)));
            if($value['Key'] == "PRI") continue;   // skipping primary keys
            /**!SECTION
             * Skippping set fields!
             */
            if($value['Field'] == "time_added") continue;   // skipping time mark
            $fields[$value['Field']] = $value['Field'];
            //error_log(sprintf("Fields is [%s], value is %s<br>\n", json_encode($fields), $value['Field']));
        }
        
        $fieldArray = $this->obj2array($this);
        
        foreach ((array)$fieldArray as $key => $val) {
            if(is_object($val)) continue;
            if(!isset($fields[$key])) continue;
            

            $updateArray[] = "`$fields[$key]` = '$val'";
        }
        $update = implode(",", $updateArray);
        
        $sql = "UPDATE $this->tableName SET  $update where id = $this->id;";
        error_log(sprintf("UPDATESQL: %s\n", $sql));
        //printf("UPDATESQL: %s\n", $sql);
        //INSERT INTO `user` (`id`, `nazwa`, `email`, `role`, `time_added`, `password`) VALUES (NULL, '123', '123', '123', current_timestamp(), '123')
        return $this->dbHandler->updateSql($sql);

    }

    /**
     * UWAGA, create nie wypełnia wszystkich pól, któe wypełnia baza danych z defaultu!
     *
     * @return [type]
     * 
     */
    public function create()
    {
        // prepare SQL first
        // get definition
        $sql = "DESCRIBE $this->tableName";
        $row = $this->dbHandler->getRowSql($sql);
        foreach ($row as $key => $value) {
            # code...
            if($value['Key'] == "PRI") continue;   // skipping primary keys
            if($value['Default'] != "") continue;   // skipping default fields
            $fields[$value['Field']] = $value['Field'];
        }
       // printf("describe is [%s]<br>\n", json_encode($row));
        
        $fieldArray = $this->obj2array($this);
        
        foreach ((array)$fieldArray as $key => $val) {
            if(is_object($val)) continue;
            if(!isset($fields[$key])) continue;
            $intoArray[] = "`".$fields[$key]."`";
            $valsArray[] = "'".$val."'";
            //error_log(sprintf("setting %s to %s [%s]<br>\n", $fields[$key], $key, $val));//$this->{$key});          
            
        }
        $into = implode(",",$intoArray);
        $vals = implode(",",$valsArray);
        $sql = "INSERT INTO `$this->tableName` ($into) VALUES ($vals);";
        //printf("SQL: %s\n", $sql);
        error_log(sprintf("SQL: %s\n", $sql));
        //INSERT INTO `user` (`id`, `nazwa`, `email`, `role`, `time_added`, `password`) VALUES (NULL, '123', '123', '123', current_timestamp(), '123')
        $inserted =  $this->dbHandler->insertSql($sql);
        if(is_string($inserted))
        {
            $this->id == -1;            
        }
        else
        {$this->id = $inserted;}
        return $this->id;
    }

    /**
     * Funkcja sprawdza, czy dwa obiekty mają bazę danych i są w niej połączone po ID
     *
     * @param mixed $obj1
     * @param mixed $obj2
     * 
     * @return -1 (i dodatkowo error w mysqli_error) jeśli nie, array jesli tak
     * 
     */
    public function matchUs($obj1, $obj2)
    {
    // sprawdź, jakiego typu jest obiekt
    $name1 =  strtolower((new \ReflectionClass($obj1))->getShortName());
    $name2 = strtolower((new \ReflectionClass($obj2))->getShortName());
    // find, if there's a table for us
    $tableName = $name1 . "_" . $name2;
    $name1 = $name1."_id";
    $name2 = $name2."_id";
    // active = 1 jest must!
    $sql = "SELECT * from `$tableName` where active = 1 and $name1 = $obj1->id and $name2 = $obj2->id;";
    $row = $this->dbHandler->getRowSql($sql);
    // printf("ROW: %s vs SQL %s, myID: [%d]<br>\n", json_encode($row), $sql, $this->id);
     if(empty($row)) return -1; // chcemy konkretnie -1
     return $row; 
    }

    public function addMeTo($object)
    {
    // sprawdź, jakiego typu jest obiekt
    $myName =  (new \ReflectionClass($this))->getShortName();
    $itName = (new \ReflectionClass($object))->getShortName();
    // find, if there's a table for us
    $tableName = strtolower($myName . "_" . $itName);
    // run values
    $into = "`". $myName. "_id`,`".$itName. "_id`";
    $vals = "'".$this->id."','" . $object->id . "'";
    // sql
    $sql = "INSERT INTO `$tableName` ($into) VALUES ($vals);";
    // execute
    return $this->dbHandler->insertSql($sql);
    }

    
    public function checkMeIn($table)
    {
        $myID = strtolower( (new \ReflectionClass($this))->getShortName() . "_id");
        $sql = "SELECT * from $table where active = 1 and $myID = $this->id;";
        $row = $this->dbHandler->getRowSql($sql);
       // printf("ROW: %s vs SQL %s, myID: [%d]<br>\n", json_encode($row), $sql, $this->id);
       // if(empty($row)) {return mysqli_error($this->dbHandler->getHandle());}
        if(empty($row)) {return -1;}    // zmieniam na -1, żeby zwracało ten sam type
        return $row; // row[0], bo pierwszy wyniczek!
    }

    /**
     * Funkcja znajduje instancję po wartości
     *
     * @param mixed $field
     * @param mixed $value
     * 
     * @return [type]
     * 
     */
    const FINDME_WRONG_FIELD = -1;
    const FINDME_NOT_FOUND = -2;

    /**
     * Zwraca ROW* obiektu w postaci, z której trzeba wyjąć $row[0]['param']!
     *
     * @param mixed $field nazwa pola, po którym szukamy
     * @param mixed $value wartość pola
     * @param mixed $active='true' tylko wśród aktywnych obiektów
     * 
     * @return int | array - FINDME_WRONG_FIELD lub FINDME_NOT_FOUND lub row z opisem pola
     * 
     */
    public function findMe($field, $value, $active='true')
    {
        if(!property_exists($this, $field)) return self::FINDME_WRONG_FIELD;
        
        
        $sql = "SELECT * from $this->tableName where active = $active and $field = $value;";
        //error_log(sprintf("FINDME: SQL %s,<br>\n",$sql));
        $row = $this->dbHandler->getRowSql($sql);
        if(!isset($row)) {return self::FINDME_NOT_FOUND;}
        if(count($row) === 0) {return self::FINDME_NOT_FOUND;}
        if(empty($row)) {return self::FINDME_NOT_FOUND;}
        //error_log(sprintf("FINDME: %s (LEN:%s) vs SQL %s, myID: [%d]<br>\n", json_encode($row), count($row),$field, $value));
        return $row; // row[0], bo pierwszy wyniczek!
    }


    public function print()
    {
    printf("<hr> PRINT for object: %s<br>\n", get_class($this));
        foreach ((array)$this as $key => $val) {
            if(is_object($val)) continue;
            printf("FIELD: %s, VALUE: [%s]<br>\n",  $key, $val);//$this->{$key});          
            
        }
        echo "<br>\n";
        return;
        //error_log(var_dump(get_object_vars($this)));
        
        return (array) $this;
        
    }

    public function getGoogleMapsLink()
    {
        //if($this instanceof Tower OR $this instanceof HomeBase OR $this instanceof Project)
        if(property_exists($this,"lat") AND property_exists($this, "lng"))
        {
        return \GPS\GPSMaps::getGoogleMapsLink($this->lat, $this->lng);
        }
        return get_class($this);
    }

    public function list($active = 'true')
    {
        $sql = "SELECT * from $this->tableName";
        if($active == "true")
        {
        $sql = "SELECT * from $this->tableName where active = true;";
        }
        
        $row = $this->dbHandler->getRowSql($sql);        
        //printf("ROW: %s<br>\n", json_encode($row));
        return $row;
    }

 

    /**
     * List all CLASS instances form DB with WHERE clause
     * 
     *
     * @param string $where - string WITHOUT where, after AND!
     * 
     * @return null | array of results
     * 
     */
    public function listWhere($active = 'true', string $where)
    {
        $sql = "SELECT * from $this->tableName where active = $active and " . $where;
        $row = $this->dbHandler->getRowSql($sql);        
        //printf("ROW: %s<br>\n", json_encode($row));
        return $row;
    }


    /**
     * Funkcja zwraca NAZWĘ stałej, jeśli ta jest zadeklarowana w klasie!
     *
     * https://stackoverflow.com/questions/1880148/how-to-get-name-of-the-constant
     * 
     * @param mixed $class Database\DBObject
     * @param mixed $value wartość
     * 
     * @return string NAZWA stałej
     * 
     */
    function getConstantName($class, $value)
    {        
        //error_log("Flipping for getConstantName: " . $class . " " . $value);
        return array_flip((new \ReflectionClass($class))->getConstants())[$value];
    }
    
    public function getClassNameUnderscore()  // if php <8.0!
    {        
        $classname = get_class($this);
        //if ($pos = strrpos($classname, '\\')) 
        return str_replace("\\","_",$classname);
        //return substr($classname, $pos + 1);
        return $classname;
    }

    public function getClassName()  // if php <8.0!
    {        
        $classname = get_class($this);
        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
        return $classname;
    }

    public function setFlag($flagName, $flagValue, $from="setFlag")
    {
        (new DBLog())->log(sprintf("SET FLAG in class %s, Object ID: %s, Flag: %s, Value: %s", get_class($this), $this->id, $flagName, $flagValue), $from);
        $this->$flagName = $flagValue;
        return;
        $fieldArray = $this->obj2array($this);
        
        foreach ((array)$fieldArray as $key => $val) {
            if(is_object($val)) continue;
            if($val != $flagName) continue;
            $this->$flagName = $flagValue;
        }

    }

}

