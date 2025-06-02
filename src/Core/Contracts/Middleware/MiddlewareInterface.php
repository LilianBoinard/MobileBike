<?php

namespace MobileBike\Core\Contracts\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareInterface
{
    /**
     * Traite une requête http entrante et retourne une réponse.
     * Délègue éventuellement la création de la réponse à un "gestionnaire"
     *
     * @param ServerRequestInterface $request
     * @param callable $next The next middleware/handler to be called
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, callable $next): ResponseInterface;
}
