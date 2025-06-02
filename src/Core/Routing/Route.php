<?php

namespace MobileBike\Core\Routing;

use MobileBike\Core\Contracts\Routing\RouteInterface;

/**
 * Classe Route - Représente une route individuelle
 *
 * Convention : Une route contient une méthode HTTP, un pattern d'URL,
 * un handler (contrôleur/action) et des paramètres optionnels
 */
class Route implements RouteInterface
{
    /**
     * @var string[] Méthodes HTTP acceptées par cette route
     */
    private array $methods;

    /**
     * @var string Pattern de l'URL (ex: /users/{id})
     */
    private string $pattern;

    /**
     * @var mixed Handler de la route (callable, contrôleur, etc.)
     */
    private mixed $handler;

    /**
     * @var string|null Nom de la route pour la génération d'URLs
     */
    private ?string $name = null;

    /**
     * @var array<string, string> Paramètres extraits de l'URL
     */
    private array $parameters = [];

    /**
     * @var array<string, string> Contraintes sur les paramètres (regex)
     */
    private array $constraints = [];

    /**
     * Constructeur de Route
     *
     * @param string|string[] $methods Méthode(s) HTTP (GET, POST, etc.)
     * @param string $pattern Pattern de l'URL
     * @param mixed $handler Handler de la route
     */
    public function __construct(string|array $methods, string $pattern, mixed $handler)
    {
        $this->methods = is_array($methods) ? $methods : [$methods];
        $this->pattern = $this->normalizePattern($pattern);
        $this->handler = $handler;
    }

    /**
     * Vérifie si cette route correspond à la requête
     *
     * @param string $method Méthode HTTP de la requête
     * @param string $path Chemin de la requête
     * @return bool True si la route correspond
     */
    public function matches(string $method, string $path): bool
    {
        // Vérifier la méthode HTTP
        if (!in_array(strtoupper($method), array_map('strtoupper', $this->methods))) {
            return false;
        }

        // Vérifier le pattern
        $regex = $this->buildRegex();
        if (!preg_match($regex, $path, $matches)) {
            return false;
        }

        // Extraire les paramètres
        $this->extractParameters($matches);
        return true;
    }

    /**
     * Définit un nom pour cette route
     *
     * @param string $name Nom de la route
     * @return Route Pour le chaining
     */
    public function name(string $name): Route
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Ajoute des contraintes sur les paramètres
     *
     * @param array<string, string> $constraints Contraintes regex par paramètre
     * @return Route Pour le chaining
     */
    public function where(array $constraints): Route
    {
        $this->constraints = array_merge($this->constraints, $constraints);
        return $this;
    }

    /**
     * Récupère le handler de la route
     */
    public function getHandler(): mixed
    {
        return $this->handler;
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
     * Récupère le nom de la route
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Récupère les méthodes HTTP supportées
     *
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Récupère le pattern de la route
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Normalise le pattern de route
     */
    private function normalizePattern(string $pattern): string
    {
        // Assurer que le pattern commence par /
        if (!str_starts_with($pattern, '/')) {
            $pattern = '/' . $pattern;
        }

        // Supprimer le / final sauf pour la racine
        if ($pattern !== '/' && str_ends_with($pattern, '/')) {
            $pattern = rtrim($pattern, '/');
        }

        return $pattern;
    }

    /**
     * Construit la regex pour matcher le pattern
     */
    private function buildRegex(): string
    {
        $pattern = $this->pattern;

        // Échapper les caractères spéciaux regex sauf les paramètres
        $pattern = preg_replace('/[.+*?\[^\]$(){}=!<>|:\-]/', '\\\\$0', $pattern);

        // Remplacer les paramètres {param} par des groupes de capture
        $pattern = preg_replace_callback('/\\\{([^}]+)\\\}/', function ($matches) {
            $paramName = $matches[1];
            $constraint = $this->constraints[$paramName] ?? '[^/]+';
            return "(?P<{$paramName}>{$constraint})";
        }, $pattern);

        return '#^' . $pattern . '$#';
    }

    /**
     * Extrait les paramètres des matches regex
     *
     * @param array<string|int, string> $matches Résultats du preg_match
     */
    private function extractParameters(array $matches): void
    {
        $this->parameters = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $this->parameters[$key] = $value;
            }
        }
    }
}