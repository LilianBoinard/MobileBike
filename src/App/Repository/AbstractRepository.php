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
    protected bool $hasPolymorphicRelations = false; // Indique si le repository gère des relations polymorphes

    /**
     * Trouve tous les enregistrements
     * Pour les repositories polymorphes, cette méthode peut être surchargée
     */
    public function findAll(): array
    {
        if ($this->hasPolymorphicRelations) {
            // Déléguer aux repositories polymorphes pour gérer les jointures
            return $this->findAllWithType();
        }

        $stmt = $this->database->query("SELECT * FROM {$this->table}");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->hydrateEntities($results);
    }

    /**
     * Trouve un enregistrement par ID
     * Pour les repositories polymorphes, cette méthode peut être surchargée
     */
    public function findById(int $id): ?object
    {
        if ($this->hasPolymorphicRelations) {
            // Déléguer aux repositories polymorphes pour gérer les jointures
            return $this->findByIdWithType($id);
        }

        error_log("DEBUG - Table: {$this->table}, PK: {$this->primaryKey}");
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
     * Pour les repositories polymorphes, cette méthode peut être surchargée
     */
    public function delete(int $id): bool
    {
        if ($this->hasPolymorphicRelations) {
            // Pour les relations polymorphes, il faut d'abord récupérer l'entité
            // pour déterminer son type et supprimer les relations
            $entity = $this->findById($id);
            if ($entity) {
                return $this->deleteEntity($entity);
            }
            return false;
        }

        $stmt = $this->database->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Supprime une entité (pour la gestion polymorphe)
     */
    protected function deleteEntity(object $entity): bool
    {
        // Par défaut, suppression simple
        // À surcharger dans les repositories polymorphes
        if (property_exists($entity, $this->primaryKey)) {
            $id = $entity->{$this->primaryKey};
            $stmt = $this->database->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
            return $stmt->execute(['id' => $id]);
        }
        return false;
    }

    /**
     * Vérifie si une valeur est disponible (n'existe pas déjà)
     */
    public function available(string $column, mixed $value): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        $stmt = $this->database->prepare($sql);
        $stmt->execute(['value' => $value]);

        return $stmt->fetchColumn() === false;
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
     * Méthodes pour les repositories polymorphes à surcharger si nécessaire
     */
    protected function findAllWithType(): array
    {
        // À implémenter dans les repositories polymorphes
        return $this->findAll();
    }

    protected function findByIdWithType(int $id): ?object
    {
        // À implémenter dans les repositories polymorphes
        return $this->findById($id);
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