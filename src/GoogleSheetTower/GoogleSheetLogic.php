<?php
/**
 * Google Sheet Logic Engine
 * GSL->RunProject($project);
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
use Database\DBObject;
use UserManagement\Organization;
use UserManagement\TowerStatus;
use UserManagement\TowerSetting;
use LOG\user_log;
class GoogleSheetLogic extends DBObject
{
    protected $gsti = null;
    /**
     * Uruchamia sprawdzanie google sheet dla wszystkich Organizacji
     *
     * @return int suma przepracowanych wież, x2 (bo i reset, i update)
     * 
     */
    public function runAll()
    {
        //(new user_log("Starting runAll", $this));
        $this->user_log("Starting runAll", $this);
        $orgList = (new Organization())->objectList();
        $result = 0;
        foreach ($orgList as $key => $value) {
            printf("Running organization %s<br>", $value->name);
            $result += $this->runOrganization($value);
            printf("DONE running organization %s<br>", $value->name);
        }
        return $result;      
    }
    public function runOrganization(\UserManagement\Organization $organization)
    {
        $result = 0;
        foreach ($organization->getChildren() as $key => $value) {
            # code...
            if($key != "project") continue;
            foreach ($value as $key1 => $value1) {
                printf("Running project %s<br>", $value1->name);
                $result += $this->runProject($value1);
                printf("DONE running project %s<br>", $value1->name);
            }
        }
        return $result;
    }
    public function runProject(\UserManagement\Project $project)
    {
        if($project->spreadsheetID == "") return -1;
        $this->gsti = new GoogleSheetTower($project->spreadsheetID);
        $result = 0;
        foreach ($project->getChildren() as $key => $value) {
            if ($key != "tower") continue;
            foreach ($value as $key1 => $value1) {
                printf("Running tower %s<br>", $value1->tower_nr);
                $result += $this->runTower($value1);
                printf("DONE running tower %s<br>", $value1->name);
            }
        }
        return $result;
    }
    public function runTower(\UserManagement\Tower $tower)
    {
      $result = $this->runTowerGetReset($tower);
      return $result + $this->runTowerUpdate($tower);
    }
    /**
     * Resetuje wszystkie flagi obiektu TowerStatus i zapisuje do bazy danych
     *
     * @param \UserManagement\Tower $tower
     * 
     * @return int 1
     * 
     */
    protected function runTowerGetReset(\UserManagement\Tower $tower)
    {
        $reset = (bool)$this->gsti->isTowerReset($tower);
        //printf("Tower [%s|%s] number: %s Has to be kept down [%d]<br>", $tower->tower_nr, $tower->name, $this->gsti->findTowerRowNumber($tower), $reset);
        if($reset)
        {
            //printf("Tower number: %s RESET !<br>", $this->gsti->findTowerRowNumber($tower));
            $towerStatus = new TowerStatus($tower);
            $towerStatus->resetAllFlags();
        }
        return 1;
    }
   
    /**
     * Ta funkcja aktualizuje jedynie google sheet danymi!
     * Jeśli wieży nie ma w pliku, dodaje,
     * Jeśli jest, usuwa
     * Funkcja nie usuwa z pliku wieży, która została przesunięta do innego projektu!
     *
     * @param \UserManagement\Tower $tower
     * 
     * @return int 1
     * 
     */
    public function runTowerUpdate(\UserManagement\Tower $tower)
    {
        $project = $tower->getTowerProject();
        if($this->gsti == null)
        $this->gsti = new GoogleSheetTower($project->spreadsheetID);
        $towerStatus = new TowerStatus($tower);
        $foundTower = $this->gsti->findTowerRowNumber($tower);
        //printf("Znaleziona wieża : %b<br>\n", (bool)$foundTower);
        $row = $this->gsti->getTowerRow($tower);  // inicjalizuję
        $found = $this->gsti->getTowerParam($tower, "Obecne koordynaty");
        printf("Obecne koordynaty : %s vs koordynaty wieży %s,%s<br>\n", $found, $tower->lat, $tower->lng);
        $found = $this->gsti->setTowerParam($tower, "Numer wiezy", $tower->tower_nr);
        $found = $this->gsti->setTowerParam($tower, "Ostatnia aktualizacja", $towerStatus->lastSeenUnsafeCopy);
        $found = $this->gsti->setTowerParam($tower, "Inwestycja", $project->name);
        //$found = $this->gsti->setTowerParam($tower, "Obecne koordynaty", $tower->currLat . "," . $tower->currLng);
        $found = $this->gsti->setTowerParam($tower, "Obecne koordynaty", sprintf("=HYPERLINK(\"%s\";\"%s\")", $tower->getGoogleMapsLink("currLat", "currLng"), $tower->startLat.",".$tower->startLng));
        $found = $this->gsti->setTowerParam($tower, "Koordynaty start", sprintf("=HYPERLINK(\"%s\";\"START: %s\")", $tower->getGoogleMapsLink("startLat", "startLng"), $tower->startLat.",".$tower->startLng));
        
        //$found = $this->gsti->setTowerParam($tower, "Koordynaty start", $tower->startLat . "," . $tower->startLng);
        $found = $this->gsti->setTowerParam($tower, "Wieza wlaczona", $towerStatus->flagLastSeen ? "NIE" : "TAK");
        $found = $this->gsti->setTowerParam($tower, "Dystans od start", $towerStatus->distanceMovedFromStart);
        $found = $this->gsti->setTowerParam($tower, "Numer obiektu", $row['Numer obiektu']);
        $found = $this->gsti->setTowerParam($tower, "Okolice", $row['Okolice']);
        $found = $this->gsti->setTowerParam($tower, "Firma podwykonawcza", $row['Firma podwykonawcza']);
        // TODO flagMovedFromStart?
        $found = $this->gsti->setTowerParam($tower, "Czas dojazdu ustalony [min.]", $towerStatus->flagMovedStart ? "NIE" :$row['Czas dojazdu ustalony [min.]']);
        $found = $this->gsti->setTowerParam($tower, "KAMERY USTAWIONE", $towerStatus->flagLastSeen ? "NIE" : $row['KAMERY USTAWIONE']);
        $found = $this->gsti->setTowerParam($tower, "Magazyn", $row['Magazyn']);
        $found = $this->gsti->setTowerParam($tower, "Uwagi", $row['Uwagi']);
        // skomentowane, na życzenie Daniela :)
        //$found = $this->gsti->setTowerParam($tower, "Link do start", $tower->getGoogleMapsLink("startLat", "startLng"));
        //$found = $this->gsti->setTowerParam($tower, "Link do obecne", $tower->getGoogleMapsLink("currLat", "currLng"));
        if ($foundTower == -1) {
            //printf("Nie można zaktualizować - nie ma w pliku!");
            $this->gsti->addTowerRow();
        } else {
            //printf("Aktualizuję?");
            $this->gsti->updateTowerRow();
        }
        return 1;
        //printf("Koniec + link [%s]<br>", PagePiece::hrefFormattedPrint($tower->getGoogleMapsLink("currLat","currLng"),"Mapa google"));
        //printf("Koniec\n<br>"); 
    }
}// koniec klasy
?>