<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\Core\Contracts\Authentication\AuthenticationInterface;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ContactController extends AbstractController
{

    public function __construct(View $view, AuthenticationInterface $authentication){
        $this->view = $view;
        $this->authentication = $authentication;
    }

    public function index(ServerRequestInterface $request): ResponseInterface {
        $user = $this->authentication->user();
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('pages/contact.html.twig', ['user' => $user])
        );
    }
}