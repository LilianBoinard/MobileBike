<?php

namespace MobileBike\App\Repository;

use Exception;
use MobileBike\Core\Contracts\Repository\RepositoryInterface;
use MobileBike\Core\Database\Database;
use PDO;

abstract class AbstractRepository implements RepositoryInterface
{
    protected Database $database;
    protected string $table;
    protected string $entityClass;
    protected string $primaryKey = 'id'; // Par défaut

    /**
     * Trouve tous les enregistrements
     */
    public function findAll(): array
    {
        $stmt = $this->database->query("SELECT * FROM {$this->table}");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->hydrateEntities($results);
    }

    /**
     * Trouve un enregistrement par ID
     */
    public function findById(int $id): ?object
    {
        $stmt = $this->database->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrateEntity($data);
    }

    /**
     * Sauvegarde une entité (création ou mise à jour)
     * Méthode abstraite à implémenter dans chaque repository
     */
    abstract public function save(object $entity): bool;

    /**
     * Supprime un enregistrement par ID
     */
    public function delete(int $id): bool
    {
        $stmt = $this->database->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifie si une valeur est disponible pour un champ donné
     */
    public function available(string $field, string $value, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$field} = :value";
        $params = ['value' => $value];

        if ($excludeUserId !== null) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params['exclude_id'] = $excludeUserId;
        }

        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() == 0;
    }

    /**
     * Hydrate une seule entité à partir des données
     */
    protected function hydrateEntity(array $data): object
    {
        return new $this->entityClass($data);
    }

    /**
     * Hydrate plusieurs entités à partir des données
     */
    protected function hydrateEntities(array $results): array
    {
        $entities = [];
        foreach ($results as $data) {
            $entities[] = $this->hydrateEntity($data);
        }
        return $entities;
    }

    /**
     * Gestion des transactions
     */
    protected function executeInTransaction(callable $callback): bool
    {
        try {
            $this->database->beginTransaction();
            $result = $callback();
            $this->database->commit();
            return $result;
        } catch (Exception $e) {
            $this->database->rollBack();
            throw $e;
        }
    }

    /**
     * Méthodes utilitaires pour la construction de requêtes
     */
    protected function buildInsertQuery(array $fields): string
    {
        $columns = implode(', ', array_keys($fields));
        $placeholders = ':' . implode(', :', array_keys($fields));

        return "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
    }

    protected function buildUpdateQuery(array $fields, ?string $whereCondition = null): string
    {
        $setParts = [];
        foreach (array_keys($fields) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }
        $setClause = implode(', ', $setParts);

        $whereClause = $whereCondition ?: "{$this->primaryKey} = :{$this->primaryKey}";

        return "UPDATE {$this->table} SET {$setClause} WHERE {$whereClause}";
    }

    /**
     * Méthodes de recherche avancées
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $whereParts = [];
        $params = [];

        foreach ($criteria as $field => $value) {
            $whereParts[] = "{$field} = :{$field}";
            $params[$field] = $value;
        }

        $sql = "SELECT * FROM {$this->table}";

        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        if ($orderBy) {
            $orderParts = [];
            foreach ($orderBy as $field => $direction) {
                $orderParts[] = "{$field} {$direction}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderParts);
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        if ($offset) {
            $sql .= " OFFSET {$offset}";
        }

        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->hydrateEntities($results);
    }

    public function findOneBy(array $criteria): ?object
    {
        $results = $this->findBy($criteria, null, 1);
        return $results[0] ?? null;
    }

    public function count(array $criteria = []): int
    {
        $whereParts = [];
        $params = [];

        foreach ($criteria as $field => $value) {
            $whereParts[] = "{$field} = :{$field}";
            $params[$field] = $value;
        }

        $sql = "SELECT COUNT(*) FROM {$this->table}";

        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Vérifie si l'entité existe
     */
    public function exists(int $id): bool
    {
        $stmt = $this->database->prepare("SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        return $stmt->fetchColumn() !== false;
    }
}