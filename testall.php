<?php

use Database\DBConfig;
use Database\DBObject;

require_once 'vendor/autoload.php'; // Autoload files using Composer autoload
echo "ok";

$db1 = new DBConfig();
$db1->load("composer.json");

?>