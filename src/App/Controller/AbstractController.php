<?php

namespace MobileBike\App\Controller;

use MobileBike\Core\Container\Container;
use MobileBike\Core\Database\Database;
use MobileBike\Core\View\View;

class AbstractController
{
    protected View $view;
    protected Database $database;
    protected Container $container;
}