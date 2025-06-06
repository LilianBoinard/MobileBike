<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\Core\Authentication\SessionAuthentication;
use MobileBike\Core\Database\Database;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController extends AbstractController
{
    public function __construct(View $view, Database $database, SessionAuthentication $authentication)
    {
        $this->view = $view;
        $this->database = $database;
        $this->authentication = $authentication;
    }

    public function index(): ResponseInterface
    {
        // Si l'utilisateur est connecté on le redirige au Dashboard
        if ($this->authentication->check()) {
            return new Response(302, ['Location' => '/dashboard']);
        }

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('pages/login.html.twig')
        );
    }
}