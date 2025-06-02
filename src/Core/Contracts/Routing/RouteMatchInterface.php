<?php

namespace MobileBike\Core\Contracts\Routing;

use MobileBike\Core\Routing\Route;

/**
 * Interface RouteMatchInterface
 *
 * Représente un résultat de correspondance de route, contenant la route et ses paramètres.
 */
interface RouteMatchInterface
{
    /**
     * Récupère la route qui a matché.
     *
     * @return Route
     */
    public function getRoute(): Route;

    /**
     * Récupère les paramètres extraits.
     *
     * @return array<string, string>
     */
    public function getParameters(): array;

    /**
     * Récupère un paramètre spécifique.
     *
     * @param string $name Nom du paramètre
     * @param string|null $default Valeur par défaut
     * @return string|null
     */
    public function getParameter(string $name, ?string $default = null): ?string;

    /**
     * Récupère le handler de la route.
     *
     * @return mixed
     */
    public function getHandler(): mixed;
}
