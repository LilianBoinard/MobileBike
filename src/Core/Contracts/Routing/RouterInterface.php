<?php

namespace MobileBike\Core\Contracts\Routing;

use MobileBike\Core\Exception\RouterException;
use MobileBike\Core\Routing\RouteMatch;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RouterInterface - Contrat pour les classes de routeur
 *
 * Définit les méthodes essentielles qu'un routeur doit implémenter
 * pour gérer l'enregistrement des routes, leur résolution et la génération d'URLs.
 *
 * Un routeur est responsable de :
 * - Collecter et organiser les routes de l'application
 * - Trouver la route correspondant à une requête HTTP
 * - Générer des URLs à partir des routes nommées
 */
interface RouterInterface
{
    /**
     * Enregistre une route GET
     *
     * @param string $pattern Pattern de l'URL (ex: '/users/{id}')
     * @param mixed $handler Handler de la route (contrôleur, callable, etc.)
     * @return RouteInterface Route créée pour configuration fluente
     */
    public function get(string $pattern, mixed $handler): RouteInterface;

    /**
     * Enregistre une route POST
     *
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return RouteInterface Route créée pour configuration fluente
     */
    public function post(string $pattern, mixed $handler): RouteInterface;

    /**
     * Enregistre une route PUT
     *
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return RouteInterface Route créée pour configuration fluente
     */
    public function put(string $pattern, mixed $handler): RouteInterface;

    /**
     * Enregistre une route PATCH
     *
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return RouteInterface Route créée pour configuration fluente
     */
    public function patch(string $pattern, mixed $handler): RouteInterface;

    /**
     * Enregistre une route DELETE
     *
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return RouteInterface Route créée pour configuration fluente
     */
    public function delete(string $pattern, mixed $handler): RouteInterface;

    /**
     * Enregistre une route pour toutes les méthodes HTTP courantes
     *
     * Méthodes supportées : GET, POST, PUT, PATCH, DELETE
     *
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return RouteInterface Route créée pour configuration fluente
     */
    public function any(string $pattern, mixed $handler): RouteInterface;

    /**
     * Enregistre une route pour des méthodes HTTP spécifiques
     *
     * @param string|string[] $methods Méthode(s) HTTP acceptée(s)
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return RouteInterface Route créée pour configuration fluente
     */
    public function match(string|array $methods, string $pattern, mixed $handler): RouteInterface;

    /**
     * Trouve la route correspondant à une requête PSR-7
     *
     * Analyse la méthode HTTP et le chemin de la requête pour
     * déterminer quelle route doit être exécutée.
     *
     * @param ServerRequestInterface $request Requête HTTP PSR-7
     * @return RouteMatch|null Résultat du matching avec route et paramètres, ou null
     */
    public function match_request(ServerRequestInterface $request): ?RouteMatch;

    /**
     * Trouve une route par méthode et chemin explicites
     *
     * Alternative à match_request() pour les cas où on a directement
     * la méthode et le chemin sans objet Request.
     *
     * @param string $method Méthode HTTP (GET, POST, etc.)
     * @param string $path Chemin de l'URL sans query string
     * @return RouteMatch|null Résultat du matching ou null si aucune correspondance
     */
    public function findRoute(string $method, string $path): ?RouteMatch;

    /**
     * Génère une URL à partir d'une route nommée
     *
     * Remplace les placeholders du pattern par les paramètres fournis
     * pour construire une URL complète.
     *
     * Exemple :
     * Route : '/users/{id}' nommée 'user.show'
     * generate('user.show', ['id' => 123]) → '/users/123'
     *
     * @param string $name Nom de la route
     * @param array<string, string|int> $parameters Paramètres pour remplacer les placeholders
     * @return string URL générée
     * @throws RouterException Si la route nommée n'existe pas ou si des paramètres sont manquants
     */
    public function generate(string $name, array $parameters = []): string;

    /**
     * Récupère toutes les routes enregistrées
     *
     * Utile pour le debugging, l'introspection ou la génération
     * de documentation des routes disponibles.
     *
     * @return RouteInterface[] Collection de toutes les routes
     */
    public function getRoutes(): array;

    /**
     * Enregistre une route dans l'index des routes nommées
     *
     * Permet d'indexer les routes ayant un nom pour la génération d'URLs.
     * Cette méthode est généralement appelée automatiquement lors de
     * l'attribution d'un nom à une route.
     *
     * @param RouteInterface $route Route à indexer par son nom
     */
    public function registerNamedRoute(RouteInterface $route): void;
}