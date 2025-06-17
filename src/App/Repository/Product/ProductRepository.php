<?php

namespace MobileBike\App\Repository\Product;

use MobileBike\App\Model\Product\MobileBike\MobileBike;
use MobileBike\App\Model\Product\MobileBike\Type\Fairing;
use MobileBike\App\Model\Product\MobileBike\Type\Recumbent;
use MobileBike\App\Model\Product\MobileBike\Type\Special;
use MobileBike\App\Model\Product\MobileBike\Type\Trikes;
use MobileBike\App\Model\Product\MobileBike\Type\Used;
use MobileBike\App\Model\Product\Product;
use MobileBike\App\Model\Product\SparePart\SparePart;
use MobileBike\App\Repository\AbstractRepository;
use MobileBike\Core\Database\Database;
use PDO;

class ProductRepository extends AbstractRepository
{
    protected string $table = 'Product';
    protected string $entityClass = Product::class;
    protected string $primaryKey = 'id_product';
    protected bool $hasPolymorphicRelations = true;

    // Mapping des types de MobileBike
    private const MOBILE_BIKE_TYPES = [
        'used' => Used::class,
        'trikes' => Trikes::class,
        'recumbent' => Recumbent::class,
        'fairing' => Fairing::class,
        'special' => Special::class,
    ];

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
            // Supprimer des tables les plus spécialisées vers les plus générales
            if ($this->isMobileBikeSubType($entity)) {
                $this->deleteMobileBikeSubType($entity);
            }

            if ($entity instanceof MobileBike) {
                $this->deleteMobileBike($entity->id_product);
            } elseif ($entity instanceof SparePart) {
                $this->deleteSparePart($entity->id_product);
            }

            // Supprimer de la table Product
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

        // Récupérer tous les sous-types de MobileBike
        foreach (self::MOBILE_BIKE_TYPES as $type => $class) {
            $products = array_merge($products, $this->findAllByMobileBikeType($type));
        }

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
        // Vérifier chaque sous-type de MobileBike
        foreach (self::MOBILE_BIKE_TYPES as $type => $class) {
            $product = $this->findMobileBikeByTypeAndId($type, $id);
            if ($product) {
                return $product;
            }
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
            'brand' => $entity->brand,
            'image' => $entity->image,
        ];

        $sql = $this->buildInsertQuery($fields);
        $stmt = $this->database->prepare($sql);
        $result = $stmt->execute($fields);

        if (!$result) {
            return false;
        }

        $entity->id_product = (int) $this->database->lastInsertId();

        // 2. Insérer dans les tables spécialisées selon le type
        if ($entity instanceof MobileBike) {
            $result = $this->createMobileBike($entity);
            if ($result && $this->isMobileBikeSubType($entity)) {
                return $this->createMobileBikeSubType($entity);
            }
            return $result;
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
            'brand' => $entity->brand,
            'image' => $entity->image,
        ];

        $sql = $this->buildUpdateQuery($fields);
        $stmt = $this->database->prepare($sql);

        $params = array_merge($fields, ['id_product' => $entity->id_product]);
        $result = $stmt->execute($params);

        if (!$result) {
            return false;
        }

        // 2. Mettre à jour les tables spécialisées selon le type
        if ($entity instanceof MobileBike) {
            $result = $this->updateMobileBike($entity);
            if ($result && $this->isMobileBikeSubType($entity)) {
                return $this->updateMobileBikeSubType($entity);
            }
            return $result;
        } elseif ($entity instanceof SparePart) {
            return $this->updateSparePart($entity);
        }

        return true;
    }

    private function createMobileBike(MobileBike $mobileBike): bool
    {
        $sql = "
            INSERT INTO MobileBike (id_product, color, material)
            VALUES (:id_product, :color, :material)
        ";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([
            'id_product' => $mobileBike->id_product,
            'color' => $mobileBike->color,
            'material' => $mobileBike->material,
        ]);
    }

    private function updateMobileBike(MobileBike $mobileBike): bool
    {
        $sql = "
            UPDATE MobileBike 
            SET color = :color, 
                material = :material
            WHERE id_product = :id_product
        ";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([
            'id_product' => $mobileBike->id_product,
            'color' => $mobileBike->color,
            'material' => $mobileBike->material,
        ]);
    }

    private function createMobileBikeSubType(MobileBike $mobileBike): bool
    {
        $tableName = $this->getMobileBikeSubTypeTable($mobileBike);
        if (!$tableName) {
            return false;
        }

        $sql = "INSERT INTO {$tableName} (id_product) VALUES (:id_product)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute(['id_product' => $mobileBike->id_product]);
    }

    private function updateMobileBikeSubType(MobileBike $mobileBike): bool
    {
        // Pour l'instant, les sous-types n'ont pas de champs spécifiques à mettre à jour
        return true;
    }

    private function createSparePart(SparePart $sparePart): bool
    {
        $sql = "INSERT INTO Spare_Part (id_product) VALUES (:id_product)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute(['id_product' => $sparePart->id_product]);
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

    private function deleteMobileBikeSubType(MobileBike $mobileBike): bool
    {
        $tableName = $this->getMobileBikeSubTypeTable($mobileBike);
        if (!$tableName) {
            return false;
        }

        $sql = "DELETE FROM {$tableName} WHERE id_product = :id_product";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute(['id_product' => $mobileBike->id_product]);
    }

    private function deleteSparePart(int $id): bool
    {
        $sql = "DELETE FROM Spare_Part WHERE id_product = :id_product";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute(['id_product' => $id]);
    }

    private function isMobileBikeSubType(object $entity): bool
    {
        foreach (self::MOBILE_BIKE_TYPES as $class) {
            if ($entity instanceof $class) {
                return true;
            }
        }
        return false;
    }

    private function getMobileBikeSubTypeTable(MobileBike $mobileBike): ?string
    {
        foreach (self::MOBILE_BIKE_TYPES as $type => $class) {
            if (get_class($mobileBike) === $class) {
                return ucfirst($type); // Utilise Used, Trikes, etc.
            }
        }
        return null;
    }

    // === MÉTHODES SPÉCIALISÉES PUBLIQUES ===

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

    public function findAllMobileBikes(): array
    {
        $sql = "
            SELECT p.*
            FROM Product p
            INNER JOIN Mobilebike mb ON p.id_product = mb.id_product
        ";

        $stmt = $this->database->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mobileBikes = [];
        foreach ($results as $row) {
            $mobileBikes[] = new MobileBike($row);
        }

        return $mobileBikes;
    }

    public function findAllByMobileBikeType(string $type): array
    {
        if (!isset(self::MOBILE_BIKE_TYPES[$type])) {
            throw new \InvalidArgumentException("Type de MobileBike inconnu: {$type}");
        }

        $tableName = ucfirst($type);
        $className = self::MOBILE_BIKE_TYPES[$type];

        $sql = "
            SELECT p.*, mb.*
            FROM Product p
            INNER JOIN MobileBike mb ON p.id_product = mb.id_product
            INNER JOIN {$tableName} t ON mb.id_product = t.id_product
        ";

        $stmt = $this->database->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($results as $row) {
            $products[] = new $className($row);
        }

        return $products;
    }

    public function findMobileBikeByTypeAndId(string $type, int $id): ?object
    {
        if (!isset(self::MOBILE_BIKE_TYPES[$type])) {
            return null;
        }

        $tableName = ucfirst($type);
        $className = self::MOBILE_BIKE_TYPES[$type];

        $sql = "
            SELECT p.*, mb.*
            FROM Product p
            INNER JOIN MobileBike mb ON p.id_product = mb.id_product
            INNER JOIN {$tableName} t ON mb.id_product = t.id_product
            WHERE p.id_product = :id
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new $className($row);
        }

        return null;
    }

    public function getProductType(int $id): ?string
    {
        // Vérifier chaque sous-type de MobileBike
        foreach (self::MOBILE_BIKE_TYPES as $type => $class) {
            $tableName = ucfirst($type);
            $sql = "SELECT COUNT(*) FROM {$tableName} WHERE id_product = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->execute(['id' => $id]);
            if ($stmt->fetchColumn() > 0) {
                return $type;
            }
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

    public function findByBrand(string $brand): array
    {
        $sql = "SELECT * FROM Product WHERE brand = :brand";
        $stmt = $this->database->prepare($sql);
        $stmt->execute(['brand' => $brand]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($results as $row) {
            // Déterminer le type et créer l'instance appropriée
            $type = $this->getProductType($row['id_product']);
            if ($type && $type !== 'spare_part') {
                $className = self::MOBILE_BIKE_TYPES[$type];
                $products[] = new $className($row);
            } elseif ($type === 'spare_part') {
                $products[] = new SparePart($row);
            }
        }

        return $products;
    }
}