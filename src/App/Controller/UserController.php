<?php

namespace MobileBike\App\Controller;

use GuzzleHttp\Psr7\Response;
use MobileBike\Core\Authentication\SessionAuthentication;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController extends AbstractController
{
    public function __construct(View $view, SessionAuthentication $authentication)
    {
        $this->view = $view;
        $this->authentication = $authentication;
    }

    public function attemptLogin(ServerRequestInterface $request): ResponseInterface
    {
        $credentials = $request->getParsedBody();

        try {
            // Délégation à votre service d'authentification
            $loginSuccessful = $this->authentication->attempt($credentials);

            if (!$loginSuccessful) {
                throw new \RuntimeException('Identifiant ou mot de passe incorrect');
            }

            // Redirection après succès
            return new Response(302, ['Location' => '/dashboard/home']);

        } catch (\Exception $e) {
            // Gestion des erreurs (mauvais credentials, etc.)
            return new Response(
                200,
                ['Content-Type' => 'text/html'],
                $this->view->twig('pages/login.html.twig', [
                    'error' => $e->getMessage()
                ])
            );
        }
    }

    public function attemptLogout(ServerRequestInterface $request): ResponseInterface {
        try {
            if ($this->authentication->check()) {
                $this->authentication->logout();
            }
            // Redirection après succès
            return new Response(302, ['Location' => '/login']);
        } catch (\Exception $e) {
            return new Response(
                200,
                ['Content-Type' => 'text/html'],
                $this->view->twig('pages/error.html.twig', [
                    'error' => $e->getMessage()
                ])
            );
        }

    }

}