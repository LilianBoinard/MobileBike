<?php

namespace MobileBike\Core\Contracts\Routing;

/**
 * Interface RouteInterface - Contrat pour les classes de route
 *
 * Définit les méthodes essentielles qu'une route doit implémenter
 * pour être compatible avec le système de routage du framework.
 *
 * Une route représente la correspondance entre une URL/méthode HTTP
 * et un handler (contrôleur/action) à exécuter.
 */
interface RouteInterface
{
    /**
     * Vérifie si cette route correspond à la requête HTTP
     *
     * @param string $method Méthode HTTP (GET, POST, PUT, DELETE, etc.)
     * @param string $path Chemin de l'URL sans query string
     * @return bool True si la route correspond à la requête
     */
    public function matches(string $method, string $path): bool;

    /**
     * Récupère le handler associé à cette route
     *
     * Le handler peut être :
     * - Une chaîne "Controller@method"
     * - Un callable/closure
     * - Toute autre forme de handler supportée
     *
     * @return mixed Handler de la route
     */
    public function getHandler(): mixed;

    /**
     * Récupère les paramètres extraits de l'URL
     *
     * Les paramètres sont extraits depuis les placeholders de l'URL
     * (ex: /users/{id} → ['id' => '123'])
     *
     * @return array<string, string> Tableau associatif des paramètres
     */
    public function getParameters(): array;

    /**
     * Récupère le nom de la route si défini
     *
     * Le nom permet de générer des URLs depuis d'autres parties
     * de l'application (liens, redirections, etc.)
     *
     * @return string|null Nom de la route ou null si non défini
     */
    public function getName(): ?string;

    /**
     * Récupère les méthodes HTTP supportées par cette route
     *
     * @return string[] Tableau des méthodes HTTP (ex: ['GET', 'POST'])
     */
    public function getMethods(): array;

    /**
     * Récupère le pattern original de la route
     *
     * @return string Pattern de l'URL (ex: '/users/{id}')
     */
    public function getPattern(): string;

    /**
     * Définit un nom pour cette route (méthode fluent)
     *
     * @param string $name Nom à attribuer à la route
     * @return self Instance courante pour le chaînage
     */
    public function name(string $name): self;

    /**
     * Ajoute des contraintes sur les paramètres de route (méthode fluent)
     *
     * Les contraintes sont des expressions régulières qui valident
     * les valeurs des paramètres de l'URL.
     *
     * Exemple : ['id' => '\d+'] pour s'assurer que 'id' est numérique
     *
     * @param array<string, string> $constraints Contraintes regex par paramètre
     * @return self Instance courante pour le chaînage
     */
    public function where(array $constraints): self;
}