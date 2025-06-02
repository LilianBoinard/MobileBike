<?php

namespace MobileBike\Core\Contracts\Database;

use PDO;
use PDOStatement;

/**
 * Interface DatabaseInterface
 *
 * Définit les opérations accessibles pour interagir avec une base de données.
 */
interface DatabaseInterface
{
    /**
     * Retourne l'instance PDO.
     *
     * @return PDO
     */
    public function getPdo(): PDO;

    /**
     * Prépare une requête SQL.
     *
     * @param string $query
     * @return PDOStatement
     */
    public function prepare(string $query): PDOStatement;

    /**
     * Exécute une requête préparée.
     *
     * @param string $query
     * @param array $params
     * @return PDOStatement
     */
    public function query(string $query, array $params = []): PDOStatement;

    /**
     * Récupère tous les résultats d'une requête.
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function fetchAll(string $query, array $params = []): array;

    /**
     * Récupère un seul résultat d'une requête.
     *
     * @param string $query
     * @param array $params
     * @return array|false
     */
    public function fetch(string $query, array $params = []);

    /**
     * Exécute une requête et retourne le nombre de lignes affectées.
     *
     * @param string $query
     * @param array $params
     * @return int
     */
    public function execute(string $query, array $params = []): int;

    /**
     * Retourne l'ID du dernier élément inséré.
     *
     * @return string
     */
    public function lastInsertId(): string;

    /**
     * Démarre une transaction.
     *
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * Valide une transaction.
     *
     * @return bool
     */
    public function commit(): bool;

    /**
     * Annule une transaction.
     *
     * @return bool
     */
    public function rollBack(): bool;

    /**
     * Vérifie si une transaction est active.
     *
     * @return bool
     */
    public function inTransaction(): bool;
}
