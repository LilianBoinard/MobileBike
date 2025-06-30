<?php

namespace MobileBike\Core\Middleware;

use GuzzleHttp\Psr7\Response;
use MobileBike\Core\Contracts\Middleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use MobileBike\Core\Contracts\Authentication\AuthenticationInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    protected AuthenticationInterface $auth;
    protected array $options;

    public function __construct(AuthenticationInterface $auth, array $options = [])
    {
        $this->auth = $auth;
        $this->options = array_merge([
            'redirectTo' => '/login',
            'whitelist' => ['login', 'home', 'about', 'services', 'products', 'contact'], // Routes exemptées de l'authentification
        ], $options);
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        // Vérifier si la route actuelle est exemptée
        $route = $request->getAttribute('route');
        $routeName = $route ? $route->getName() : null;

        if ($this->isWhitelisted($routeName)) {
            return $next($request);
        }

        // Vérifier l'authentification
        if (!$this->auth->check()) {
            return $this->redirectToLogin($request);
        }

        // Ajouter l'utilisateur à la requête pour les couches suivantes
        $request = $request->withAttribute('user', $this->auth->user());

        return $next($request);
    }

    protected function isWhitelisted(?string $routeName): bool
    {
        return in_array($routeName, $this->options['whitelist']);
    }

    protected function redirectToLogin(ServerRequestInterface $request): ResponseInterface
    {
        // Implémentation basique de redirection
        // Dans une vraie application, utiliser une ResponseFactory
        // Redirection vers la page de connexion ou message d'erreur
        return new Response(
            302,
            ['Location' => $this->options['redirectTo']],
        );
    }
}