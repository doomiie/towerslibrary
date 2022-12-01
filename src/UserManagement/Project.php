<?php
/**
 * Projekt:
 * 
 * Nadrzędna w stosunku do wieży i użytkownika
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
class Project extends \Database\DBObject
{
    protected $tableName = "project";
    protected $parentClass = "organization";
    protected $childrenClassArray = array('tower','email');

    public $spreadsheetID;
    public $spreadsheetLink;

    public function print()
    {
        parent::print();
        // Project powinna być w organizacji
        $row = $this->checkMeIn("project_organization");
        if(is_string($row))
        {
            printf("PROBLEM: project is not in organization, error: %s  <br>\n", $row);
        //return -1;
        }
        $projectID = (int)$row[0]['organization_id'];
        printf("Tower project ID: %s  [%s]<br>\n", json_encode($row), $row[0]['organization_id']);
        $project = new Organization((int)$projectID);
        printf("<hr> THIS project belongs to organization: %s from %s<br>\n", $project->name, $projectID);
        $project->print();
    }

    /**
     * [Description for getAllTowers]
     *
     * @return array lista wszystkich wież dla projektu
     * 
     */
    public function getAllTowers()
    {
        $towerArray = [];
        $towerList = (new Tower())->list();
        foreach ($towerList as $key => $value) {
            # code...
            $tower = new Tower((int)$value['id']);
            if(-1 === $tower->matchUs($tower, $this))
            {
                continue;
            }
            // znaleziona wieża
            $towerArray[] = $tower;
        }
        return $towerArray;
    }

    public function getProjectOrganization()
    {
        $row = $this->checkMeIn("project_organization");
        if (is_string($row)) {
            return null;
        } else {
            $organizationID = (int)$row[0]['organization_id'];
            $organization = new Organization((int)$organizationID);
            return $organization;
        }
    }
}
