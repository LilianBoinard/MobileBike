<?php

use MobileBike\Core\Bootstrap;

require_once '../vendor/autoload.php';

$application = Bootstrap::init();
$application->runWithFallback();