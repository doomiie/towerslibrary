<?php
/**
 * Problem table
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
use UserManagement\Priviledge;
use UserManagement\Tower;
use UserManagement\Project;
class SystemProblem extends \Database\DBObject
{
    protected $tableName = "problems";
    public $refType;
    public $refID;
    const PROBLEM_UNUSED_PRIVILEDGE = 1;
    const PROBLEM_TOWER_WITHOUT_PROJECT = 2;
    const PROBLEM_PROJECT_WITHOUR_ORGANIZATION = 4;
    const PROBLEM_ORGANIZATION_WITHOUT_PROJECT = 8;
    const PROBLEM_PROJECT_WITHOUT_TOWER = 16;
    const PROBLEM_HOMEBASE_WITHOUT_PROJECT = 32;
    const PROBLEM_PROJECT_WITHOUT_HOMEBASE = 64;
    const PROBLEM_TOWER_WITHOUT_SETTING = 128;
    const PROBLEM_PROJECT_GOOGLE_SHEET = 256;
    
    public function runSystemCheck()
    {
        // clear table
        $this->dbHandler->getRowSql("TRUNCATE table problems;");
        // dla każdej metody dla tej klasy :)
        foreach (get_class_methods($this) as $key => $value) {
            # code...
            if(strpos($value,"findProblem",0) === 0)
            {
               // $this->log(sprintf("Running: %s vs %s<br>\n", $key, json_encode($value)), false);
                $result = $this->$value();
                //error_log(sprintf("Result of %s  is [%s]<br>\n", $value, $result));
            }
        }
    }
    public function add($message, $refType, $refID, $name="")
    {
        //printf("describe is [%s]<br>\n", $this->message));
        $this->message = $message;
        $this->name = $name;
        $this->refID = $refID;
        $this->refType = $refType;
        return $this->create();
    }
    
    public function findProblemProjectWithoudGoogleSheet()
    {        //(new \LOG\DBLog())->log(__FILE__ ."|". __LINE__ ."|". __CLASS__ ."|". __FUNCTION__);
        $projectList = (new Project())->objectList();
        foreach ($projectList as $key => $value) {
            # code...
            # Sprawdzam Spreadsheet ID
            if($value->spreadsheetID = "" ) {
            $result = $this->add("Projekt nie ma zdefiniowanego arkusza google", self::PROBLEM_PROJECT_GOOGLE_SHEET, $value->id, $value->name );
            }
            //if($value->getHomeBase())
        
        }
        //$this->log(sprintf("Result ok %s<br>\n", $result));
    }
    
    public function findProblemUnusedPriviledge()
    {
        //(new \LOG\DBLog())->log(__FILE__ ."|". __LINE__ ."|". __CLASS__ ."|". __FUNCTION__);
        return $this->findProbGeneric("UserManagement\Priviledge","user_priviledge","Dostęp nieprzypisany do żadnego użytkownika",self::PROBLEM_UNUSED_PRIVILEDGE);
    }

    public function findProblemTowersWithoutProjects()
    {
        return $this->findProbGeneric("UserManagement\Tower","tower_project","Wieża nieprzypisana do żadnego projektu",self::PROBLEM_TOWER_WITHOUT_PROJECT);
    }    
    public function findProblemTowersWithoutSettings()
    {
        return $this->findProbGeneric("UserManagement\Tower","tower_setting","Wieża bez konfiguracji",self::PROBLEM_TOWER_WITHOUT_SETTING);
    }
    public function findProblemProjectWithoutTowers()
    {
        return $this->findProbGeneric("UserManagement\Project","tower_project","Projekt bez wież",self::PROBLEM_PROJECT_WITHOUT_TOWER);
    }
    public function findProblemProjectWithourOrganization()
    {
        return $this->findProbGeneric("UserManagement\Project","project_organization","Projekt nieprzypisany do organizacji",self::PROBLEM_PROJECT_WITHOUR_ORGANIZATION);
    }
    public function findProblemOrganizationWithoutProject()
    {
        return $this->findProbGeneric("UserManagement\Organization","project_organization","Organizacja bez projektu",self::PROBLEM_ORGANIZATION_WITHOUT_PROJECT);
    }
    public function findProblemHomebaseWithoutProjectt()
    {
        return $this->findProbGeneric("UserManagement\HomeBase","homebase_project","Baza bez projektu",self::PROBLEM_HOMEBASE_WITHOUT_PROJECT);
    }
    public function findProblemProjectWithoudHomeBase()
    {
        return $this->findProbGeneric("UserManagement\Project","homebase_project","Projekt bez Bazy",self::PROBLEM_PROJECT_WITHOUT_HOMEBASE);
    }
    public function findProbGeneric($class, $table, $message, $problemID)
    {
        //$this->log(sprintf("[%d]Szukam problemu dla klasy: %s w tabeli %s<br>\n",$problemID, $class, json_encode($table)));
        $counter = 0;
        $privList = (new $class())->list();
        foreach ($privList as $key => $value) {
            # code...
            //echo $key . " " . json_encode($value);
            //error_log(sprintf("FPR: %s vs %s<br>\n", $key, json_encode($value)));
            $priv = new $class((int)$value['id']);
            //$priv->print();
            $res = $priv->checkMeIn($table);
            //echo json_encode($res) . "<br>";
            if(($res == -1))
            {
                $counter++;
                $this->add($message, $problemID, $priv->id, $priv->name);
                //echo json_encode("Pusty res, znalazłem ćwoka <br>");
            }
            else
            {
                //echo json_encode("Zasadniczo dua la " . $class . " " . $table . PHP_EOL . "<br>");
            }
        }
        return $counter;
    }
    public static function classNameFromProblem(int $refType)
    {
            switch ($refType) {
                case 1: $className = "UserManagement\Priviledge"; break;
                case 2: 
                case 128: 
                    $className = "UserManagement\Tower"; break;
                case 4: 
                case 16: 
                case 64: 
                case 256:
                    $className = "UserManagement\Project"; break;
                case 8: $className = "UserManagement\Organization"; break;
                case 32: $className = "UserManagement\HomeBase"; break;
                default:
                    $className="";
                    break;
            }
            return $className;
    }
}
