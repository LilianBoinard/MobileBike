<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Repository\User\UserRepository;
use MobileBike\Core\Contracts\Authentication\AuthenticationInterface;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(View $view, AuthenticationInterface $authentication){
        $this->view = $view;
        $this->authentication = $authentication;
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->authentication->user();
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('pages/home.html.twig', ['user' => $user])
        );
    }
}