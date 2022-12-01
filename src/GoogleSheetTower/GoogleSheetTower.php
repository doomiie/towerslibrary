<?php

/**
 * Google Sheet Interface tower
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
//require_once 'vendor/autoload.php'; // Autoload files using Composer autoload
use UserManagement\TowerStatus;
use UserManagement\Tower;

class GoogleSheetTower extends GoogleSheetTowerInterface
{
  public function updateTower($tower, $flagKamery = "NIE", $flagCzas = "NIE")
  {
    $this->log(sprintf("Znaleziona wieża : %s<br>\n", $tower->id));
    $towerStatus = new TowerStatus($tower);
    $project = $tower->getTowerProject();
    //$tower->print();
    $foundTower = $this->findTowerRowNumber($tower);
    $this->log(sprintf("Znaleziona wieża : ID %s<br>\n", $foundTower),false);
    $row = $this->getTowerRow($tower);  // inicjalizuję
    $found = $this->getTowerParam($tower, "Obecne koordynaty");
    //  printf("Obecne koordynaty : %s vs koordynaty wieży %s,%s<br>\n", $found, $tower->lat, $tower->lng);
    $found = $this->setTowerParam($tower, "Numer wiezy", $tower->tower_nr);
    $found = $this->setTowerParam($tower, "Ostatnia aktualizacja", $towerStatus->lastSeenUnsafeCopy);
    $found = $this->setTowerParam($tower, "Inwestycja", $project->name);
    $found = $this->setTowerParam($tower, "Obecne koordynaty", $tower->currLat . "," . $tower->currLng);
    $found = $this->setTowerParam($tower, "Koordynaty start", $tower->startLat . "," . $tower->startLng);
    $found = $this->setTowerParam($tower, "Wieza wlaczona", $towerStatus->active ? "TAK" : "NIE");
    $found = $this->setTowerParam($tower, "Dystans od start", $towerStatus->distanceMovedFromStart);

    if($foundTower == -1)
    {
      $found = $this->setTowerParam($tower, "Czas dojazdu ustalony [min.]", $flagKamery);
      $found = $this->setTowerParam($tower, "KAMERY USTAWIONE", $flagCzas);
      $found = $this->setTowerParam($tower, "Numer obiektu", "Uzupełnij");
      $found = $this->setTowerParam($tower, "Okolice", "Uzupełnij");
      $found = $this->setTowerParam($tower, "Firma podwykonawcza", "Uzupełnij");
      $found = $this->setTowerParam($tower, "Magazyn", "Uzupełnij");
      $found = $this->setTowerParam($tower, "Uwagi", "Brak");
    }

    $found = $this->setTowerParam($tower, "Link do start", $tower->getGoogleMapsLink("startLat", "startLng"));
    $found = $this->setTowerParam($tower, "Link do obecne", $tower->getGoogleMapsLink("currLat", "currLng"));
    if ($foundTower == -1) {
      //printf("Nie można zaktualizować - nie ma w pliku!");
      $this->addTowerRow();
    } else {
      //printf("Aktualizuję?");
      $this->updateTowerRow();
    }
    //printf("Koniec + link [%s]<br>", PagePiece::hrefFormattedPrint($tower->getGoogleMapsLink("currLat","currLng"),"Mapa google"));
  }


  /**
   * Czy w pliku są ustawione dane
   * getTowerReset pobiera z pliku pozycję KAMERY USTAWIONE i Czas dojazdu ustalony [min.]
   * Jeśli któraś z tych opcji jest równa "NIE", zwraca false
   * Jeśli obie są różne (czyli jest wszystko poustawiane), zwraca true
   *
   * @param mixed $tower Wieża, której szukamy w pliku
   * 
   * @return bool
   * 
   */
  public function isTowerReset($tower)
  {
    $foundTower = $this->findTowerRowNumber($tower);
    if ($foundTower == -1) { return -1; }
    $this->log("Taking reset " . $foundTower);
    $row = $this->getTowerRow($tower); 
    //return json_encode($row);
    $kameryUstawione = $row['KAMERY USTAWIONE'];
    $czasDojasduUstawiony = $row['Czas dojazdu ustalony [min.]'];
    if($kameryUstawione == "NIE" OR $czasDojasduUstawiony == "NIE") { return false;}
    return true;
    
  }

  /**
   * Zwraca flagę
   *
   * @param mixed $tower
   * @param mixed $flagName - KAMERY USTAWIONE lub Czas dojazdu ustalony [min.]
   * 
   * @return [type]
   * 
   */
  public function getFlag($tower, $flagName)
  {
    $foundTower = $this->findTowerRowNumber($tower);
    if ($foundTower == -1) { return -1; }
    
    $row = $this->getTowerRow($tower); 
    
    $returnFlag = $row[$flagName];
    return $returnFlag;
  }
}
