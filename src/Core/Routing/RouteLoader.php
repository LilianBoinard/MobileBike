<?php

namespace MobileBike\Core\Routing;

use MobileBike\Core\Contracts\Routing\RouteLoaderInterface;
use MobileBike\Core\Exception\RouteLoaderException;

/**
 * Chargeur de routes PHP simple
 *
 * Charge les routes depuis un fichier routes.php
 */
class RouteLoader implements RouteLoaderInterface
{
    /**
     * Charge les routes depuis un fichier PHP
     *
     * @param string $filePath Chemin vers le fichier routes.php
     * @param Router $router Instance du router à configurer
     * @return void
     * @throws RouteLoaderException En cas d'erreur
     */
    public static function load(string $filePath, Router $router): void
    {
        if (!file_exists($filePath)) {
            throw new RouteLoaderException("Fichier de routes '$filePath' introuvable");
        }

        if (!is_readable($filePath)) {
            throw new RouteLoaderException("Fichier de routes '$filePath' non lisible");
        }

        // Inclure le fichier avec le router disponible
        // Le fichier peut utiliser $router directement
        require $filePath;
    }

    /**
     * Charge les routes avec gestion d'erreurs
     *
     * @param string $filePath Chemin vers le fichier routes.php
     * @param Router $router Instance du router
     * @return bool True si chargé avec succès
     */
    public static function loadSafely(string $filePath, Router $router): bool
    {
        try {
            self::load($filePath, $router);
            return true;
        } catch (RouteLoaderException $e) {
            // Log l'erreur si nécessaire
            error_log("Erreur chargement routes: " . $e->getMessage());
            return false;
        }
    }
}