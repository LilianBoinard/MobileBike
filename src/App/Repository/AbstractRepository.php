<?php

namespace MobileBike\App\Repository;

use MobileBike\Core\Contracts\Repository\RepositoryInterface;
use MobileBike\Core\Database\Database;
use PDO;

abstract class AbstractRepository implements RepositoryInterface
{

    protected Database $database;
    protected string $table;
    protected string $entityClass;

    public function findAll(): array
    {
        $stmt = $this->database->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->database->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);

        $entity = $stmt->fetch();
        return $entity ?: null;
    }

    abstract public function save(object $entity): bool;


    public function delete(int $id): bool
    {
        $stmt = $this->database->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function available(string $column, mixed $value): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        $stmt = $this->database->prepare($sql);
        $stmt->execute(['value' => $value]);

        return $stmt->fetchColumn() === false;
    }

}