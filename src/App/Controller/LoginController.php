<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Controller\AbstractController;
use MobileBike\Core\Container\Container;
use MobileBike\Core\Database\Database;
use MobileBike\Core\View\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class LoginController extends AbstractController
{
    public function __construct(View $view, Database $database)
    {
        $this->view = $view;
        $this->database = $database;
    }

    public function index(): ResponseInterface
    {
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('pages/login.html.twig')
        );
    }
}