<?php

namespace MobileBike\Core\Routing;

use MobileBike\Core\Contracts\Routing\RouteMatchInterface;

/**
 * Classe RouteMatch - Résultat d'un matching de route
 *
 * Contient la route trouvée et ses paramètres
 */
readonly class RouteMatch implements RouteMatchInterface
{
    /**
     * @param Route $route Route qui a matché
     * @param array<string, string> $parameters Paramètres extraits
     */
    public function __construct(
        private Route $route,
        private array $parameters
    ) {
    }

    /**
     * Récupère la route qui a matché
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * Récupère les paramètres extraits
     *
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Récupère un paramètre spécifique
     *
     * @param string $name Nom du paramètre
     * @param string|null $default Valeur par défaut
     */
    public function getParameter(string $name, ?string $default = null): ?string
    {
        return $this->parameters[$name] ?? $default;
    }

    /**
     * Récupère le handler de la route
     */
    public function getHandler(): mixed
    {
        return $this->route->getHandler();
    }
}