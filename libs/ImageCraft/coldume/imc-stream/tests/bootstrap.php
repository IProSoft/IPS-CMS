<?php

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->addPsr4('ImcStream\\', __DIR__);

\TranslatedException\TranslatedException::init();

date_default_timezone_set('UTC');
