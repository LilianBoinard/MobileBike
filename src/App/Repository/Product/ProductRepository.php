<?php

namespace MobileBike\App\Repository\Product;

use MobileBike\App\Model\Product\Product;
use MobileBike\App\Repository\AbstractRepository;
use MobileBike\Core\Database\Database;

class ProductRepository extends AbstractRepository
{
    protected string $table = 'products';
    protected string $entityClass = Product::class;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }


    public function save(object $entity): bool
    {
        if (!$entity instanceof Product) {
            throw new \InvalidArgumentException('L\'entité doit être une instance de Product');
        }

        if ($entity->id) {
            // Mise à jour
            $stmt = $this->database->prepare("
                        UPDATE {$this->table} 
                        SET name = :name, 
                            description = :description, 
                            price = :price,
                            stock = :stock
                        WHERE id = :id
            ");
            return $stmt->execute([
                'id' => $entity->id,
                'name' => $entity->name,
                'description' => $entity->description,
                'price' => $entity->price,
                'stock' => $entity->stock,
            ]);
        }

        $stmt = $this->database->prepare("
        INSERT INTO {$this->table} (name, description, price, stock)
        VALUES (:name, :description, :price, :stock)");
        $result = $stmt->execute([
            'name' => $entity->name,
            'description' => $entity->description,
            'price' => $entity->price,
            'stock' => $entity->stock,
        ]);

        if ($result) {
            $entity->id = (int) $this->database->lastInsertId();
        }

        return $result;
    }
}