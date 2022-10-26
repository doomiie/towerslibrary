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

class GoogleSheetTowerInterface
{
    public function __construct()
    {
        $client = new \Google_Client();
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        // credentials.json is the key file we downloaded while setting up our Google Sheets API
        $path = 'logowaniesandbox-b6f0bd572f18.json';
        $client->setAuthConfig($path);

        $service = new \Google_Service_Sheets($client);

        $spreadsheetId = '1GHkXALjjAx9c7xW4EbCBrAJJxr4RgltlRVYuKpGe39E';
    }
}
