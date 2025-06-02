<?php

namespace MobileBike\Core\Http\Middleware;

use MobileBike\Core\Contracts\Middleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Gestionnaire de middlewares - Implémentation du pattern Chain of Responsibility
 *
 * Gère l'exécution séquentielle des middlewares sous forme de pipeline.
 * Chaque middleware peut modifier la requête avant de passer au suivant,
 * et/ou modifier la réponse après exécution du suivant (pattern "oignon").
 *
 * Flux d'exécution :
 * Request → Middleware1 → Middleware2 → Controller → Middleware2 → Middleware1 → Response
 */
class MiddlewareHandler
{
    /**
     * @var array Liste des middlewares à exécuter dans l'ordre d'ajout
     */
    private array $middlewares = [];

    /**
     * @var callable La fonction finale à exécuter (généralement le contrôleur)
     */
    private $controller;

    /**
     * Ajoute un middleware à la pile d'exécution
     *
     * Les middlewares sont exécutés dans l'ordre d'ajout pour la requête,
     * et dans l'ordre inverse pour la réponse.
     *
     * @param MiddlewareInterface $middleware Middleware à ajouter
     * @return self Pour permettre le chaînage fluent
     */
    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Définit le contrôleur final du pipeline
     *
     * Le contrôleur est appelé après tous les middlewares de requête
     * et avant tous les middlewares de réponse.
     *
     * @param callable $controller Fonction/méthode du contrôleur à exécuter
     * @return self Pour permettre le chaînage fluent
     */
    public function setController(callable $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Exécute la pile de middlewares et le contrôleur final
     *
     * Utilise array_reduce pour construire un pipeline en "oignon" :
     * - Chaque middleware enveloppe le suivant
     * - Le contrôleur est au centre
     * - L'exécution se fait de l'extérieur vers l'intérieur puis retour
     *
     * @param ServerRequestInterface $request Requête HTTP à traiter
     * @return ResponseInterface Réponse HTTP après traitement complet
     * @throws \Exception Si le contrôleur n'est pas défini
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Vérification que le contrôleur est défini
        if (!$this->controller) {
            throw new \RuntimeException('Aucun contrôleur défini pour le middleware handler');
        }

        // Fonction core qui exécute le contrôleur final
        $core = function (ServerRequestInterface $request) {
            return call_user_func($this->controller, $request);
        };

        // Construction du pipeline en "oignon" via array_reduce
        // array_reverse car on veut que le premier middleware ajouté soit le plus externe
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            function ($next, MiddlewareInterface $middleware) {
                // Chaque middleware retourne une fonction qui encapsule la suivante
                return function (ServerRequestInterface $request) use ($middleware, $next) {
                    return $middleware->process($request, $next);
                };
            },
            $core // Point de départ : le contrôleur au centre
        );

        // Exécution du pipeline complet
        return $pipeline($request);
    }
}