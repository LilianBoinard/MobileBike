<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Repository\User\UserRepository;
use MobileBike\Core\Contracts\Authentication\AuthenticationInterface;
use MobileBike\Core\Exception\Exceptions\UnauthorizedException;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DashboardController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(View $view, AuthenticationInterface $authentication, UserRepository $userRepository){
        $this->view = $view;
        $this->authentication = $authentication;
        $this->userRepository = $userRepository;
    }

    public function index(ServerRequestInterface $request): ResponseInterface {

        // Vérification d'autorisation
        $user = $this->authentication->user();
        if (!$user){
            throw new UnauthorizedException();
        }

        // Récuperation des roles
        $isClient = $this->userRepository->isClient($user->id_user);
        $isAdmin = $this->userRepository->isAdministrator($user->id_user);

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('dashboard/dashboard.html.twig', [
                'user' => $user,
                'isClient' => $isClient,
                'isAdmin' => $isAdmin
            ])
        );
    }
}