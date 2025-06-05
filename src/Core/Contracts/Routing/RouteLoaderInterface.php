<?php

namespace MobileBike\Core\Contracts\Routing;

use MobileBike\Core\Exception\Exceptions\RouteLoaderException;
use MobileBike\Core\Routing\Router;

/**
 * Interface pour les chargeurs de routes
 *
 * Définit le contrat pour charger des routes depuis différentes sources
 */
interface RouteLoaderInterface
{
    /**
     * Charge les routes depuis une source
     *
     * @param string $filePath Source des routes (fichier, URL, etc.)
     * @param Router $router Instance du router à configurer
     * @return void
     * @throws RouteLoaderException En cas d'erreur de chargement
     */
    public static function load(string $filePath, Router $router): void;

    /**
     * Charge les routes avec gestion d'erreurs
     *
     * @param string $filePath Source des routes
     * @param Router $router Instance du router
     * @return bool True si chargé avec succès, false sinon
     */
    public static function loadSafely(string $filePath, Router $router): bool;

    /**
     * Vérifie si la source est valide et accessible
     *
     * @param string $source Source à vérifier
     * @return bool True si la source est valide
     */
}