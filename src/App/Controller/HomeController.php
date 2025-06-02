<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Repository\UserRepository;
use MobileBike\Core\Database\Database;
use MobileBike\Core\View\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
        $this->database = $this->container->get(Database::class);
        $this->userRepository = new UserRepository($this->database);
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->userRepository->findByUsername('admin');
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            View::twig('pages/home.html.twig', ['user' => $user])
        );
    }
}