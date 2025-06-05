<?php

namespace MobileBike\Core\Database;

use Exception;
use MobileBike\Core\Contracts\Database\DatabaseInterface;
use PDO;
use PDOException;

/**
 * Classe Database compatible avec l'injection de dépendances
 */
class Database implements DatabaseInterface
{
    private PDO $pdo;
    private array $config;

    /**
     * Constructeur qui accepte la configuration de la base de données
     *
     * @throws Exception Si la connexion échoue
     */
    public function __construct()
    {
        $this->config = $this->getDefaultConfig();
        $this->connect();
    }

    /**
     * Établit la connexion à la base de données
     *
     * @throws Exception Si la connexion échoue
     */
    private function connect(): void
    {
        try {
            $dsn = $this->buildDsn();
            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
        } catch (PDOException $e) {
            throw new Exception('Erreur de connexion à la base de données: ' . $e->getMessage());
        }
    }

    /**
     * Construit le DSN à partir de la configuration
     *
     * @return string
     */
    private function buildDsn(): string
    {
        return sprintf(
            '%s:host=%s;dbname=%s;charset=%s',
            $this->config['driver'],
            $this->config['host'],
            $this->config['database'],
            $this->config['charset']
        );
    }

    /**
     * Retourne la configuration par défaut
     *
     * @return array
     */
    private function getDefaultConfig(): array
    {
        return [
            'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'database' => $_ENV['DB_NAME'] ?? '',
            'username' => $_ENV['DB_USER'] ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => filter_var($_ENV['DB_PERSISTENT'] ?? false, FILTER_VALIDATE_BOOLEAN),
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . ($_ENV['DB_CHARSET'] ?? 'utf8mb4')
            ]
        ];
    }

    /**
     * Retourne l'instance PDO
     *
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Prépare une requête SQL
     *
     * @param string $query
     * @return \PDOStatement
     */
    public function prepare(string $query): \PDOStatement
    {
        return $this->pdo->prepare($query);
    }

    /**
     * Méthode magique pour déléguer les appels à PDO
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return $this->pdo->$method(...$args);
    }

    /**
     * Exécute une requête préparée
     *
     * @param string $query
     * @param array $params
     * @return \PDOStatement
     */
    public function query(string $query, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Récupère tous les résultats d'une requête
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function fetchAll(string $query, array $params = []): array
    {
        return $this->query($query, $params)->fetchAll();
    }

    /**
     * Récupère un seul résultat d'une requête
     *
     * @param string $query
     * @param array $params
     * @return array|false
     */
    public function fetch(string $query, array $params = [])
    {
        return $this->query($query, $params)->fetch();
    }

    /**
     * Exécute une requête et retourne le nombre de lignes affectées
     *
     * @param string $query
     * @param array $params
     * @return int
     */
    public function execute(string $query, array $params = []): int
    {
        return $this->query($query, $params)->rowCount();
    }

    /**
     * Retourne l'ID du dernier élément inséré
     *
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Démarre une transaction
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Valide une transaction
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Annule une transaction
     *
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Vérifie si une transaction est active
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }
}