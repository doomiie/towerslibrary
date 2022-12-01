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

class GoogleSheetObject {

    public $service;
    public $spreadsheetId;
    public $client;


    public function __construct()
    {
        $this->client = new \Google_Client();
        $this->client->setScopes([\Google_Service_Sheets::SPREADSHEETS, Google_Service_Drive::DRIVE_FILE,
        Google_Service_Drive::DRIVE,
        Google_Service_Drive::DRIVE_METADATA_READONLY]);
        //$this->client->addScope(\Google_Service_Drive::DRIVE);
            //Google\Service\Drive::DRIVE);               // for adding new sheets
        $this->client->setAccessType('offline');
        // credentials.json is the key file we downloaded while setting up our Google Sheets API
        $path = 'logowaniesandbox-b6f0bd572f18.json';
        $this->client->setAuthConfig($path);

        $this->service = new \Google_Service_Sheets($this->client);

        //$this->spreadsheetId = '1GHkXALjjAx9c7xW4EbCBrAJJxr4RgltlRVYuKpGe39E';
    }

    public function create($title)
    {   
        /* Load pre-authorized user credentials from the environment.
           TODO(developer) - See https://developers.google.com/identity for
            guides on implementing OAuth2 for your application. */
        
        try{

            $spreadsheet = new \Google_Service_Sheets_Spreadsheet([
                'properties' => [
                    'title' => $title
                    ]
                ]);
                $spreadsheet = $this->service->spreadsheets->create($spreadsheet, [
                    'fields' => 'spreadsheetId'
                ]);
                printf("Spreadsheet ID: %s\n", $spreadsheet->spreadsheetId);
                return $spreadsheet->spreadsheetId;
        }
        catch(Exception $e) {
            // TODO(developer) - handle error appropriately
            echo 'Message: ' .$e->getMessage();
          }
    }

    public function shareFile($fileId, $emailAddress)
    {
        try {
            $client = new $this->client;
            $driveService = new \Google\Service\Drive($client);
            //$client->addScope(Drive::DRIVE);
            $ids = array();
                $driveService->getClient()->setUseBatch(true);
                try {
                    $batch = $driveService->createBatch();
    
                    $userPermission = new \Google\Service\Drive\Permission(array([
                        'type' => 'user',
                        'role' => 'writer',
                        'emailAddress' => $emailAddress
                    ]));
                    $userPermission['emailAddress'] = $emailAddress;
                    $request = $driveService->permissions->create(
                        $fileId, $userPermission, array(['fields' => 'id']));
                    $batch->add($request, 'user');
                    
                    $results = $batch->execute();
    
                    foreach ($results as $result) {
                        if ($result instanceof \Google_Service_Exception) {
                            // Handle error
                            printf($result);
                        } else {
                            printf("Permission ID: %s\n", $result->id);
                            array_push($ids, $result->id);
                        }
                    }
                } finally {
                    $driveService->getClient()->setUseBatch(false);
                }
                return $ids;
        } catch(Exception $e) {
            echo "Error Message: ".$e;
        }
    
    }

    public static function getGoogleSheetLinkFromId($googleSheetID)
    {
        return "https://docs.google.com/spreadsheets/d/". $googleSheetID;

    }


 } // end of class {GoogleSheetObject} 




