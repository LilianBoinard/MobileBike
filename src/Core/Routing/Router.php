<?php

namespace MobileBike\Core\Routing;

use MobileBike\Core\Contracts\Routing\RouteInterface;
use MobileBike\Core\Contracts\Routing\RouterInterface;
use MobileBike\Core\Exception\Exceptions\RouterException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Classe Router - Gestionnaire de routes
 *
 * Convention : Le router collecte les routes, trouve la bonne route
 * pour une requête donnée, et peut générer des URLs
 */
class Router implements RouterInterface
{
    /**
     * @var Route[] Collection des routes enregistrées
     */
    private array $routes = [];

    /**
     * @var array<string, Route> Routes indexées par nom
     */
    private array $namedRoutes = [];

    /**
     * @var ContainerInterface|null Container pour l'injection de dépendances
     */
    private ?ContainerInterface $container = null;

    /**
     * Constructeur du Router
     *
     * @param ContainerInterface|null $container Container optionnel
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Ajoute une route GET
     *
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return Route La route créée
     */
    public function get(string $pattern, mixed $handler): Route
    {
        return $this->addRoute('GET', $pattern, $handler);
    }

    /**
     * Ajoute une route POST
     *
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return Route La route créée
     */
    public function post(string $pattern, mixed $handler): Route
    {
        return $this->addRoute('POST', $pattern, $handler);
    }

    /**
     * Ajoute une route PUT
     *
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return Route La route créée
     */
    public function put(string $pattern, mixed $handler): Route
    {
        return $this->addRoute('PUT', $pattern, $handler);
    }

    /**
     * Ajoute une route PATCH
     *
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return Route La route créée
     */
    public function patch(string $pattern, mixed $handler): Route
    {
        return $this->addRoute('PATCH', $pattern, $handler);
    }

    /**
     * Ajoute une route DELETE
     *
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return Route La route créée
     */
    public function delete(string $pattern, mixed $handler): Route
    {
        return $this->addRoute('DELETE', $pattern, $handler);
    }

    /**
     * Ajoute une route pour toutes les méthodes
     *
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return Route La route créée
     */
    public function any(string $pattern, mixed $handler): Route
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $pattern, $handler);
    }



    /**
     * Ajoute une route avec méthodes personnalisées
     *
     * @param string|string[] $methods Méthode(s) HTTP
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return Route La route créée
     */
    public function match(string|array $methods, string $pattern, mixed $handler): Route
    {
        return $this->addRoute($methods, $pattern, $handler);
    }

    /**
     * Trouve la route correspondant à une requête
     *
     * @param ServerRequestInterface $request Requête PSR-7
     * @return RouteMatch|null Résultat du matching ou null si pas trouvé
     */
    public function match_request(ServerRequestInterface $request): ?RouteMatch
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        return $this->findRoute($method, $path);
    }

    /**
     * Trouve une route par méthode et chemin
     *
     * @param string $method Méthode HTTP
     * @param string $path Chemin de l'URL
     * @return RouteMatch|null Résultat du matching
     */
    public function findRoute(string $method, string $path): ?RouteMatch
    {
        foreach ($this->routes as $route) {
            if ($route->matches($method, $path)) {
                return new RouteMatch($route, $route->getParameters());
            }
        }

        return null;
    }

    /**
     * Génère une URL pour une route nommée
     *
     * @param string $name Nom de la route
     * @param array<string, string> $parameters Paramètres pour l'URL
     * @return string URL générée
     * @throws RouterException Si la route n'existe pas
     */
    public function generate(string $name, array $parameters = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new RouterException("Route nommée '$name' introuvable");
        }

        $route = $this->namedRoutes[$name];
        $pattern = $route->getPattern();

        // Remplacer les paramètres dans le pattern
        foreach ($parameters as $key => $value) {
            $pattern = str_replace("{{$key}}", (string)$value, $pattern);
        }

        // Vérifier qu'il ne reste pas de paramètres non remplacés
        if (preg_match('/\{[^}]+\}/', $pattern)) {
            throw new RouterException("Paramètres manquants pour générer l'URL de la route '$name'");
        }

        return $pattern;
    }

    /**
     * Récupère toutes les routes
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Ajoute une route à la collection
     *
     * @param string|string[] $methods Méthode(s) HTTP
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     * @return Route La route créée
     */
    private function addRoute(string|array $methods, string $pattern, mixed $handler): Route
    {
        $route = new Route($methods, $pattern, $handler);
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Enregistre une route nommée
     *
     * @param Route | RouteInterface $route Route à enregistrer
     */
    public function registerNamedRoute(Route|RouteInterface $route): void
    {
        $name = $route->getName();
        if ($name !== null) {
            $this->namedRoutes[$name] = $route;
        }
    }
}