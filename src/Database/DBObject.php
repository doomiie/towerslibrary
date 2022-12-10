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
use LOG\user_log;

class DBObject
{
    protected $tableName = "";
    /**
     * Próbuję zainicjować z -1 w tym miejscu, żeby dla insert nie kasować ID, tylko zwracać -1
     *
     * @var [type]
     */
    public $id = -1;
    public $name;
    public $active;
    public $time_added;
    /**
     * Timestamp, when the row was updated.
     * ALTER TABLE `*`  ADD `time_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  ON UPDATE CURRENT_TIMESTAMP AFTER `refID`;
     *
     * @var [type]
     */
    public $time_updated;

    public $dbHandler;

    /**
     * Nazwa klasy "rodzica"
     * Dla tower -> project -> organization
     * Dla homebase -> project
     *
     * @var [type]
     */
    protected $parentClass;
    protected $childrenClassArray;


    public function __construct($id = null)
    {
        $this->dbHandler = new DBHandler();
        $this->id = -1;
        //error_log("Konstruktor klasy " . $this->getClassName(). ", id is " . $id);
        if (!is_null($id)) {
            $this->load($id);
            return;
        }
    }

    protected function obj2array(&$instance)
    {
        $clone = (array) $instance;
        $rtn = array();
        $rtn['___SOURCE_KEYS_'] = $clone;

        while (list($key, $value) = each($clone)) {
            $aux = explode("\0", $key);
            $newkey = $aux[count($aux) - 1];
            $rtn[$newkey] = &$rtn['___SOURCE_KEYS_'][$key];
        }

        return $rtn;
    }

    /**
     * Loads ALL fields that are in the database !
     *
     * @param int $id ID of field
     * @param string $id NAME of field!
     * 
     * @return [type] fields affected
     * 
     */
    public function load($id)
    {

        $table = $this->tableName;
        if (is_int($id)) {
            $sql = "select * from $table where id = '$id'";
            $row = $this->dbHandler->getRowSql($sql);
            if (empty($row)) {
                return 0;
            }
            $row = $row[0];
        }
        if (is_string($id)) {
            $sql = "select * from $table where name = '$id'";
            $row = $this->dbHandler->getRowSql($sql);
            if (empty($row)) {
                return 0;
            }
            $row = $row[0];
        }
        if(is_array($id))
        {
            error_log(sprintf("[%s] loading array: %s\n", __FUNCTION__, json_encode($id)));

            $row = $id;
            //$this->loadArray($id);
        }
        // what if there's no data or error?
        // $row = $this->dbHandler->getRowSql($sql);
        
        
        //echo "Loading" . json_encode($row);

        $fieldArray = $this->obj2array($this);
        $fieldCounter = 0;
        foreach ((array)$fieldArray as $key => $val) {
            if (is_object($val)) continue;
            $key = htmlspecialchars(trim($key));
            if (isset($row[$key])) {
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

        //error_log(sprintf("UPDATE FOR %s<br>\n", get_class($this)));
        $sql = "DESCRIBE $this->tableName";
        $row = $this->dbHandler->getRowSql($sql);
        foreach ($row as $key => $value) {
            # code...
            //error_log(sprintf("key [%s] is [%s]<br>\n", $key, json_encode($value)));
            if ($value['Key'] == "PRI") continue;   // skipping primary keys
            /**!SECTION
             * Skippping set fields!
             */
            if ($value['Field'] == "time_added") continue;   // skipping time mark
            $fields[$value['Field']] = $value['Field'];
            //error_log(sprintf("Fields is [%s], value is %s<br>\n", json_encode($fields), $value['Field']));
        }

        $fieldArray = $this->obj2array($this);

        foreach ((array)$fieldArray as $key => $val) {
            if (is_object($val)) continue;
            if (!isset($fields[$key])) continue;


            $updateArray[] = "`$fields[$key]` = '$val'";
        }
        $update = implode(",", $updateArray);

        $sql = "UPDATE $this->tableName SET  $update where id = $this->id;";
        //error_log(sprintf("UPDATESQL: %s\n", $sql));
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
        //error_log(sprintf("describe is [%s]<br>\n", json_encode($row)));
        foreach ($row as $key => $value) {
            # code...
            $name = $value['Field'];
           
            if ($value['Key'] == "PRI") continue;   // skipping primary keys
            // FIXME
            if ($value['Default'])
            if (property_exists($this, $name)) {
                //$this->log(sprintf("TEST ROW: %s", $this->$name));
                if($this->$name == "") continue;   // skipping default fields (DISABLED, checking)
            }
            $fields[$value['Field']] = $value['Field'];
        }

        $fieldArray = $this->obj2array($this);

        foreach ((array)$fieldArray as $key => $val) {
            if (is_object($val)) continue;
            if (!isset($fields[$key])) continue;
            $intoArray[] = "`" . $fields[$key] . "`";
            $valsArray[] = "'" . $val . "'";
            //error_log(sprintf("setting %s to %s [%s]<br>\n", $fields[$key], $key, $val));//$this->{$key});          

        }
        $into = implode(",", $intoArray);
        $vals = implode(",", $valsArray);
        $sql = "INSERT INTO `$this->tableName` ($into) VALUES ($vals);";
        //printf("SQL: %s\n", $sql);
        //$this->log(sprintf("SQL: %s\n", $sql));
        
        //INSERT INTO `user` (`id`, `nazwa`, `email`, `role`, `time_added`, `password`) VALUES (NULL, '123', '123', '123', current_timestamp(), '123')
        $inserted =  $this->dbHandler->insertSql($sql);
        
        //$this->log(sprintf("InsertSQL res: %s\n", $inserted));
        if (is_string($inserted)) {
            //REVIEW - zobaczmy, czy zwracanie -1 jest lepsze od ustawiania -1 na id
            //$this->id = -1;
            return -1;
        } else {
            $this->id = $inserted;
        }
        return $this->id;
    }

    /**
     * Usuwam obiekt z bazy danych!
     *
     * @return [type]
     * 
     */
    public function deleteMe()
    {
        // znajdź powiązania!
        $parent = $this->getParent();
        if($parent != null)
        {
            $myName =  (new \ReflectionClass($this))->getShortName();
            $itName = (new \ReflectionClass($parent))->getShortName();
            // find, if there's a table for us
            $tableName = strtolower($myName . "_" . $itName);
            $sql = sprintf("DELETE from %s where %s_id = %s;", $tableName, strtolower($myName), $this->id);
        //    error_log("DELETE SQL: !" . $sql);
            $row = $this->dbHandler->getRowSql($sql);

        }
        //error_log("Deleting!");
        
        $sql = "DELETE from $this->tableName where id = $this->id;";
        $this->log("Deleting myself from $this->tableName, id = $this->id");
        $this->log("SQL : " . $sql);
        $row = $this->dbHandler->getRowSql($sql);
        return count($row);
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
        $name1 = $name1 . "_id";
        $name2 = $name2 . "_id";
        // active = 1 jest must!
        $sql = "SELECT * from `$tableName` where active = 1 and $name1 = $obj1->id and $name2 = $obj2->id;";
        $row = $this->dbHandler->getRowSql($sql);
        // printf("ROW: %s vs SQL %s, myID: [%d]<br>\n", json_encode($row), $sql, $this->id);
        if (empty($row)) return -1; // chcemy konkretnie -1
        return $row;
    }

    /**
     * Funkcja matchująca dwa obiekty (bez sprawdzania, czy matchowanie jest już gdzie indziej)
     *
     * @param mixed $object
     * 
     * @return [type]
     * 
     */
    public function addMeTo($object)
    {
        // sprawdź, jakiego typu jest obiekt
        $myName =  (new \ReflectionClass($this))->getShortName();
        $itName = (new \ReflectionClass($object))->getShortName();
        // find, if there's a table for us
        $tableName = strtolower($myName . "_" . $itName);
        // run values
        $into = "`" . $myName . "_id`,`" . $itName . "_id`";
        $vals = "'" . $this->id . "','" . $object->id . "'";
        // sql
        $sql = "INSERT INTO `$tableName` ($into) VALUES ($vals);";
        // execute
        return $this->dbHandler->insertSql($sql);
    }


    public function checkMeIn($table)
    {
        $myID = strtolower((new \ReflectionClass($this))->getShortName() . "_id");
        $sql = "SELECT * from $table where active = 1 and $myID = $this->id;";
        $row = $this->dbHandler->getRowSql($sql);
        //$this->log(sprintf("ROW: %s vs SQL %s, myID: [%d]<br>\n", json_encode($row), $sql, $this->id));
       
        // if(empty($row)) {return mysqli_error($this->dbHandler->getHandle());}
        if (empty($row)) {
            return -1;
        }    // zmieniam na -1, żeby zwracało ten sam type
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
    public function findMe($field, $value, $active = 'true')
    {
        if (!property_exists($this, $field)) return self::FINDME_WRONG_FIELD;
        $sql = "SELECT * from $this->tableName where active = $active and $field = $value;";
        //error_log(sprintf("FINDME: SQL %s,<br>\n",$sql));
        $row = $this->dbHandler->getRowSql($sql);
        if (!isset($row)) {
            return self::FINDME_NOT_FOUND;
        }
        if (count($row) === 0) {
            return self::FINDME_NOT_FOUND;
        }
        if (empty($row)) {
            return self::FINDME_NOT_FOUND;
        }
        //error_log(sprintf("FINDME: %s (LEN:%s) vs SQL %s, myID: [%d]<br>\n", json_encode($row), count($row),$field, $value));
        return $row; // row[0], bo pierwszy wyniczek!
    }

    function removeMeFrom($table)
    {
    }


    public function print()
    {
        printf("<hr> PRINT for object: %s<br>\n", get_class($this));
        foreach ((array)$this as $key => $val) {
            if (is_object($val)) continue;
            if(is_array($val)) {printf("FIELD: %s, VALUE: [%s]<br>\n",  $key, json_encode($val)); }
            else
            printf("FIELD: %s, VALUE: [%s]<br>\n",  $key, $val);

        }
        echo "<br>\n";
        return;
        //error_log(var_dump(get_object_vars($this)));

        return (array) $this;
    }

    /**
     * Zwraza link do google maps, niepreformatowany, z możliwością zmiany nazwy lat/lng (np na startLat, startLng)
     *
     * @param string $lat
     * @param string $lng
     * 
     * @return [type]
     * 
     */
    public function getGoogleMapsLink($lat = "lat", $lng = "lng", $title = "")
    {
        //if($this instanceof Tower OR $this instanceof HomeBase OR $this instanceof Project)
        if (property_exists($this, $lat) and property_exists($this, $lng)) {
            return \GPS\GPSMaps::getGoogleMapsLink($this->$lat, $this->$lng, $title);   // uwaga, zmienne! 
        }
        return get_class($this);
    }

    public function list($active = 'true', $where = '')
    {
        $sql = "SELECT * from $this->tableName";
        if ($active == "true" && $where == '') {
            $sql = "SELECT * from $this->tableName where active = true;";
        }
        if ($active == "true" && $where != '') {
            $sql = "SELECT * from $this->tableName where $where AND active = true;";
        }


        $row = $this->dbHandler->getRowSql($sql);
        //printf("ROW: %s<br>\n", json_encode($row));
        return $row;
    }


    /**
     * Funkcja zwraca listę obiektów danej klasy
     * Uwaga - ponieważ i tak ładujemy do row[], zmieniam na funkcję ładującą z Array właśnie
     
     * @param string $active
     * @param string where BEZ where i bez spacji
     * 
     * @return mixed objectList lista obiektów
     * 
     */
    public function objectList($active = 'true', $where = "", $limit = "")
    {
        $objectList = null;
        $sql = "SELECT * from $this->tableName " . $where . " " . $limit;
        if ($active == "true" && $where != "") {
            $sql = "SELECT * from $this->tableName where $where and active = true  " . $limit;
        }
        else
        if ($active == "true" ) {
            $sql = "SELECT * from $this->tableName where active = true " . $limit;
        }
        $row = $this->dbHandler->getRowSql($sql);
        //printf("ROW: %s<br>\n", json_encode($row));
        //printf("SQL: %s<br>\n", json_encode($sql));
        foreach ($row as $key => $value) {
            # code...
            error_log(sprintf("TEST key: %s\n", json_encode($key)));
            $class =  get_class($this);
            //$objectList[] = new $class((int)$value['id']);
            $objectList[] = new $class((Array)$value);
        }
       // error_log(sprintf("objectList: %s\n", json_encode($objectList)));
        return $objectList;
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
        return str_replace("\\", "_", $classname);
        //return substr($classname, $pos + 1);
        return $classname;
    }

    public function getClassName()  // if php <8.0!
    {
        $classname = get_class($this);
        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
        return $classname;
    }

    public function setFlag($flagName, $flagValue, $from = "setFlag")
    {
        
        if ($this->$flagName != $flagValue) {
            $this->log(sprintf("SET FLAG in class %s, Object ID: %s, Flag: %s, Value: %s", get_class($this), $this->id, $flagName, $flagValue), $from);
            //(new DBLog())->log(sprintf("SET FLAG in class %s, Object ID: %s, Flag: %s, Value: %s", get_class($this), $this->id, $flagName, $flagValue), $from);
        }
        $this->$flagName = $flagValue;
        
        return true;        
    }

    public function linkToSinglePage()
    {
        return sprintf("https://tools4teams.pl/single-%s.php?id=%s", strtolower($this->getClassName()), $this->id);
    }

    public function getParent()
    {
        /**
         * Jeśli parentClass = "project"
         * to parentTable = tower_project
         * id = project_id
         */
        $myName =  (new \ReflectionClass($this))->getShortName();
        //  $itName = (new \ReflectionClass($object))->getShortName();
        // find, if there's a table for us
        $tableName = strtolower($myName . "_" . $this->parentClass);
        //$tableName = strtolower($this->tableName . "_" . $this->parentClass);
        //printf("getParent table name is %s<br>", $tableName);
        $row = $this->checkMeIn($tableName);
        if (is_string($row)) {
            return null;
        } else {
            //var_dump($row);
            $parentID = (int)$row[0][$this->parentClass . '_id'];
            $fullClass = "UserManagement\\" . ucfirst($this->parentClass);
            $object = new $fullClass((int)$parentID);
            return $object;
        }
    }

    // TODO 
    public function getChildren()
    {
        foreach ($this->childrenClassArray as $key => $value) {
            # code...
            $tableName = strtolower($value . "_" . $this->tableName);
           // $this->log(sprintf("CHILD CLASS: %s", $tableName));
            $fullClass = "UserManagement\\" . ucfirst($value);
            $objectList = (new $fullClass())->objectList();
            foreach ($objectList as $key1 => $value1) {
                # code...
                //$row = $this->checkMeIn($tableName);                
                $row = $this->matchUs($value1, $this);
                //$this->log(sprintf("Child id: %s %d\r\n", $value1->id, json_encode($row)));
                if($row == -1) { continue;}
                $resultArray[$value][] = $value1;
                unset($row);
            }


        }
        return $resultArray;
    }
   
    // LOGGING functions here!
    public function log($message, $trace = false, $customFileName = false)
    {
//        $debugBacktrace = json_encode(debug_backtrace());
        $home = "/var/www/gps"; 
        if(isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] != "")
        $home = $_SERVER['DOCUMENT_ROOT'];
             // nie działą dla CLI, command line;
        
        $fileName = sprintf("%s/log/%s%s.log",$home,$customFileName?$this->name:"",date("Y-n-d"));

        $text = sprintf("[%s][%s][%s] \r\n",date("G-i-s"), $this->getClassName()."-", $this->id);
        //error_log("HOME is :" . $home);
        //error_log($text);
        error_log($text, 3, $fileName);
        error_log($message."\r\n", 3, $fileName);
        //error_log($message."\r\n");
        if(!$trace) return;
        $e = new \Exception();
        $debugBacktrace = sprintf("%s\r\n",$e->getTraceAsString());
        error_log($debugBacktrace, 3, $fileName);
        //error_log($debugBacktrace);
    }

    public function user_log($message)
    {
           (new user_log($message, $this));
    }
  
}
