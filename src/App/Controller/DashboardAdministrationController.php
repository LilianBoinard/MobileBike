<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\App\Controller\AbstractController;
use MobileBike\App\Repository\User\UserRepository;
use MobileBike\Core\Contracts\Authentication\AuthenticationInterface;
use MobileBike\Core\Exception\Exceptions\UnauthorizedException;
use MobileBike\Core\View\View;

class DashboardAdministrationController extends AbstractController
{

    private UserRepository $userRepository;

    public function __construct(View $view, AuthenticationInterface $authentication, UserRepository $userRepository){
        $this->view = $view;
        $this->authentication = $authentication;
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        // Vérification d'autorisation
        $user = $this->authentication->user();
        $isAdmin = $this->userRepository->isAdministrator($user->id);
        if (!$isAdmin){
            throw new UnauthorizedException();
        }

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $this->view->twig('dashboard/administration.html.twig', [
                'user' => $user,
                'isClient' => true,
                'isAdmin' => true
            ])
        );
    }
}