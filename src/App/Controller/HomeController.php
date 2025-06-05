<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Repository\User\UserRepository;
use MobileBike\Core\Database\Database;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(View $view, Database $database){
        $this->view = $view;
        $this->database = $database;
        $this->userRepository = new UserRepository($this->database);
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->userRepository->findByUsername('admin');
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('pages/home.html.twig', ['user' => $user])
        );
    }
}