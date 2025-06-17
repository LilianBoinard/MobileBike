<?php

namespace MobileBike\App\Repository\Product;

use MobileBike\App\Model\Product\MobileBike\MobileBike;
use MobileBike\App\Model\Product\Product;
use MobileBike\App\Model\Product\SparePart\SparePart;
use MobileBike\App\Repository\AbstractRepository;
use MobileBike\Core\Database\Database;
use PDO;

class ProductRepository extends AbstractRepository
{
    protected string $table = 'product';
    protected string $entityClass = Product::class;
    protected string $primaryKey = 'id_product';
    protected bool $hasPolymorphicRelations = true; // Indique que ce repository gère des relations polymorphes

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Sauvegarde polymorphe qui gère tous les types de produits
     */
    public function save(object $entity): bool
    {
        if (!$entity instanceof Product) {
            throw new \InvalidArgumentException('L\'entité doit être une instance de Product');
        }

        return $this->executeInTransaction(function () use ($entity) {
            if ($entity->id_product) {
                return $this->updateProduct($entity);
            } else {
                return $this->createProduct($entity);
            }
        });
    }

    /**
     * Surcharge pour gérer les relations polymorphes
     */
    protected function deleteEntity(object $entity): bool
    {
        if (!$entity instanceof Product) {
            throw new \InvalidArgumentException('L\'entité doit être une instance de Product');
        }

        return $this->executeInTransaction(function () use ($entity) {
            // Supprimer d'abord des tables spécialisées (contraintes FK)
            if ($entity instanceof MobileBike) {
                $this->deleteMobileBike($entity->id_product);
            } elseif ($entity instanceof SparePart) {
                $this->deleteSparePart($entity->id_product);
            }

            // Puis supprimer de la table Product
            $stmt = $this->database->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
            return $stmt->execute(['id' => $entity->id_product]);
        });
    }

    /**
     * Surcharge pour les relations polymorphes
     */
    protected function findAllWithType(): array
    {
        $products = [];

        // Récupérer les MobileBikes
        $mobileBikes = $this->findAllMobileBikes();
        $products = array_merge($products, $mobileBikes);

        // Récupérer les SpareParts
        $spareParts = $this->findAllSpareParts();
        $products = array_merge($products, $spareParts);

        return $products;
    }

    /**
     * Surcharge pour les relations polymorphes
     */
    protected function findByIdWithType(int $id): ?object
    {
        // D'abord vérifier si c'est un MobileBike
        $sql = "
            SELECT p.*, mb.*
            FROM Product p
            INNER JOIN MobileBike mb ON p.id_product = mb.id_product
            WHERE p.id_product = :id
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new MobileBike($row);
        }

        // Vérifier si c'est un SparePart
        $sql = "
            SELECT p.*
            FROM Product p
            INNER JOIN Spare_Part sp ON p.id_product = sp.id_product
            WHERE p.id_product = :id
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new SparePart($row);
        }

        return null;
    }

    // === MÉTHODES PRIVÉES POUR LA GESTION POLYMORPHE ===

    private function createProduct(Product $entity): bool
    {
        // 1. Insérer dans la table Product
        $fields = [
            'name' => $entity->name,
            'description' => $entity->description,
            'price' => $entity->price,
            'stock' => $entity->stock,
        ];

        $sql = $this->buildInsertQuery($fields);
        $stmt = $this->database->prepare($sql);
        $result = $stmt->execute($fields);

        if (!$result) {
            return false;
        }

        $entity->id_product = (int) $this->database->lastInsertId();

        // 2. Insérer dans la table spécialisée selon le type
        if ($entity instanceof MobileBike) {
            return $this->createMobileBike($entity);
        } elseif ($entity instanceof SparePart) {
            return $this->createSparePart($entity);
        }

        return true;
    }

    private function updateProduct(Product $entity): bool
    {
        // 1. Mettre à jour la table Product
        $fields = [
            'name' => $entity->name,
            'description' => $entity->description,
            'price' => $entity->price,
            'stock' => $entity->stock,
        ];

        $sql = $this->buildUpdateQuery($fields);
        $stmt = $this->database->prepare($sql);

        $params = array_merge($fields, ['id_product' => $entity->id_product]);
        $result = $stmt->execute($params);

        if (!$result) {
            return false;
        }

        // 2. Mettre à jour la table spécialisée selon le type
        if ($entity instanceof MobileBike) {
            return $this->updateMobileBike($entity);
        } elseif ($entity instanceof SparePart) {
            return $this->updateSparePart($entity);
        }

        return true;
    }

    private function createMobileBike(MobileBike $mobileBike): bool
    {
        $sql = "
            INSERT INTO MobileBike (id_product, image, color, material, brand)
            VALUES (:id_product, :image, :color, :material, :brand)
        ";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([
            'id_product' => $mobileBike->id_product,
            'image' => $mobileBike->image,
            'color' => $mobileBike->color,
            'material' => $mobileBike->material,
            'brand' => $mobileBike->brand
        ]);
    }

    private function updateMobileBike(MobileBike $mobileBike): bool
    {
        $sql = "
            UPDATE MobileBike 
            SET image = :image, 
                color = :color, 
                material = :material, 
                brand = :brand
            WHERE id_product = :id_product
        ";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([
            'id_product' => $mobileBike->id_product,
            'image' => $mobileBike->image,
            'color' => $mobileBike->color,
            'material' => $mobileBike->material,
            'brand' => $mobileBike->brand
        ]);
    }

    private function createSparePart(SparePart $sparePart): bool
    {
        $sql = "
            INSERT INTO Spare_Part (id_product)
            VALUES (:id_product)
        ";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([
            'id_product' => $sparePart->id_product
        ]);
    }

    private function updateSparePart(SparePart $sparePart): bool
    {
        // Pour l'instant, SparePart n'a pas de champs spécifiques à mettre à jour
        return true;
    }

    private function deleteMobileBike(int $id): bool
    {
        $sql = "DELETE FROM MobileBike WHERE id_product = :id_product";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute(['id_product' => $id]);
    }

    private function deleteSparePart(int $id): bool
    {
        $sql = "DELETE FROM Spare_Part WHERE id_product = :id_product";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute(['id_product' => $id]);
    }

    // === MÉTHODES SPÉCIALISÉES PUBLIQUES ===

    public function findAllMobileBikes(): array
    {
        $sql = "
            SELECT p.*, mb.*
            FROM Product p
            INNER JOIN MobileBike mb ON p.id_product = mb.id_product
        ";

        $stmt = $this->database->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mobileBikes = [];
        foreach ($results as $row) {
            $mobileBikes[] = new MobileBike($row);
        }

        return $mobileBikes;
    }

    public function findAllSpareParts(): array
    {
        $sql = "
            SELECT p.*
            FROM Product p
            INNER JOIN Spare_Part sp ON p.id_product = sp.id_product
        ";

        $stmt = $this->database->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $spareParts = [];
        foreach ($results as $row) {
            $spareParts[] = new SparePart($row);
        }

        return $spareParts;
    }

    public function getProductType(int $id): ?string
    {
        // Vérifier MobileBike
        $sql = "SELECT COUNT(*) FROM MobileBike WHERE id_product = :id";
        $stmt = $this->database->prepare($sql);
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            return 'mobile_bike';
        }

        // Vérifier SparePart
        $sql = "SELECT COUNT(*) FROM Spare_Part WHERE id_product = :id";
        $stmt = $this->database->prepare($sql);
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            return 'spare_part';
        }

        return null;
    }

    public function findByInStock(): array
    {
        return $this->findBy(['stock' => true]);
    }

    public function findMobileBikesByBrand(string $brand): array
    {
        $sql = "
            SELECT p.*, mb.*
            FROM Product p
            INNER JOIN MobileBike mb ON p.id_product = mb.id_product
            WHERE mb.brand = :brand
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['brand' => $brand]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mobileBikes = [];
        foreach ($results as $row) {
            $mobileBikes[] = new MobileBike($row);
        }

        return $mobileBikes;
    }
}