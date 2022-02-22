<?php

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->addPsr4('TranslatedException\\', __DIR__);

date_default_timezone_set('UTC');
