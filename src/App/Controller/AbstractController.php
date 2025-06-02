<?php

namespace MobileBike\App\Controller;

use MobileBike\Core\Container\Container;
use MobileBike\Core\Database\Database;

class AbstractController
{
    protected Database $database;
    protected Container $container;
}