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
    protected string $primaryKey = 'id';
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
            if ($entity->id) {
                return $this->updateProduct($entity);
            } else {
                return $this->createProduct($entity);
            }
        });
    }

    /**
     * Supprime un produit avec ses relations polymorphes
     */
    public function delete(int $id): bool
    {
        $product = $this->findByIdWithType($id);
        if (!$product) {
            return false;
        }

        return $this->executeInTransaction(function () use ($product) {
            // Supprimer des tables les plus spécialisées vers les plus générales
            if ($this->isMobileBikeSubType($product)) {
                $this->deleteMobileBikeSubType($product);
            }

            if ($product instanceof MobileBike) {
                $this->deleteMobileBike($product->id);
            } elseif ($product instanceof SparePart) {
                $this->deleteSparePart($product->id);
            }

            // Supprimer de la table Product
            return parent::delete($product->id);
        });
    }

    /**
     * Trouve tous les produits avec leur type spécifique
     */
    public function findAll(): array
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
     * Trouve un produit par ID avec son type spécifique
     */
    public function findById(int $id): ?object
    {
        return $this->findByIdWithType($id);
    }

    /**
     * Trouve un produit par ID avec les relations polymorphes
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
            INNER JOIN Spare_Part sp ON p.id = sp.product_id
            WHERE p.id = :id
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new SparePart($row);
        }

        return null;
    }

    /**
     * Trouve tous les produits en stock
     */
    public function findInStock(): array
    {
        return $this->findBy(['stock_quantity' => ['>', 0]]);
    }

    /**
     * Trouve les produits par marque
     */
    public function findByBrand(string $brand): array
    {
        $sql = "SELECT * FROM Product WHERE brand = :brand";
        $stmt = $this->database->prepare($sql);
        $stmt->execute(['brand' => $brand]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($results as $row) {
            // Déterminer le type et créer l'instance appropriée
            $type = $this->getProductType($row['id']);
            if ($type && $type !== 'spare_part') {
                $className = self::MOBILE_BIKE_TYPES[$type];
                $products[] = new $className($row);
            } elseif ($type === 'spare_part') {
                $products[] = new SparePart($row);
            }
        }

        return $products;
    }

    /**
     * Trouve les produits par gamme de prix
     */
    public function findByPriceRange(float $minPrice, float $maxPrice): array
    {
        $sql = "
            SELECT * FROM Product 
            WHERE price BETWEEN :min_price AND :max_price
            ORDER BY price ASC
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            'min_price' => $minPrice,
            'max_price' => $maxPrice
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->hydrateProductsWithType($results);
    }

    /**
     * Recherche de produits par nom
     */
    public function searchByName(string $searchTerm): array
    {
        $sql = "
            SELECT * FROM Product 
            WHERE name LIKE :search_term
            ORDER BY name ASC
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['search_term' => "%{$searchTerm}%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->hydrateProductsWithType($results);
    }

    // === MÉTHODES SPÉCIALISÉES PUBLIQUES ===

    /**
     * Trouve tous les SpareParts
     */
    public function findAllSpareParts(): array
    {
        $sql = "
            SELECT p.*
            FROM Product p
            INNER JOIN Spare_Part sp ON p.id = sp.product_id
        ";

        $stmt = $this->database->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $spareParts = [];
        foreach ($results as $row) {
            $spareParts[] = new SparePart($row);
        }

        return $spareParts;
    }

    /**
     * Trouve tous les MobileBikes (sans distinction de sous-type)
     */
    public function findAllMobileBikes(): array
    {
        $sql = "
            SELECT p.*, mb.*
            FROM Product p
            INNER JOIN MobileBike mb ON p.id = mb.product_id
        ";

        $stmt = $this->database->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mobileBikes = [];
        foreach ($results as $row) {
            $mobileBikes[] = new MobileBike($row);
        }

        return $mobileBikes;
    }

    /**
     * Trouve tous les produits d'un type de MobileBike spécifique
     */
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
            INNER JOIN MobileBike mb ON p.id = mb.product_id
            INNER JOIN {$tableName} t ON mb.product_id = t.product_id
        ";

        $stmt = $this->database->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($results as $row) {
            $products[] = new $className($row);
        }

        return $products;
    }

    /**
     * Trouve un MobileBike par type et ID
     */
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
            INNER JOIN MobileBike mb ON p.id = mb.product_id
            INNER JOIN {$tableName} t ON mb.product_id = t.product_id
            WHERE p.id = :id
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new $className($row);
        }

        return null;
    }

    /**
     * Détermine le type d'un produit
     */
    public function getProductType(int $id): ?string
    {
        // Vérifier chaque sous-type de MobileBike
        foreach (self::MOBILE_BIKE_TYPES as $type => $class) {
            $tableName = ucfirst($type);
            $sql = "SELECT COUNT(*) FROM {$tableName} WHERE product_id = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->execute(['id' => $id]);
            if ($stmt->fetchColumn() > 0) {
                return $type;
            }
        }

        // Vérifier SparePart
        $sql = "SELECT COUNT(*) FROM Spare_Part WHERE product_id = :id";
        $stmt = $this->database->prepare($sql);
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            return 'spare_part';
        }

        return null;
    }

    /**
     * Met à jour le stock d'un produit
     */
    public function updateStock(int $productId, int $quantity): bool
    {
        $sql = "UPDATE Product SET stock_quantity = :quantity WHERE id = :id";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([
            'quantity' => $quantity,
            'id' => $productId
        ]);
    }

    /**
     * Décrémente le stock d'un produit
     */
    public function decrementStock(int $productId, int $quantity): bool
    {
        $sql = "
            UPDATE Product 
            SET stock_quantity = stock_quantity - :quantity 
            WHERE id = :id AND stock_quantity >= :quantity
        ";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([
            'quantity' => $quantity,
            'id' => $productId
        ]);
    }

    // === MÉTHODES PRIVÉES POUR LA GESTION POLYMORPHE ===

    private function createProduct(Product $entity): bool
    {
        // 1. Insérer dans la table Product
        $fields = [
            'name' => $entity->name,
            'short_description' => $entity->short_description,
            'long_description' => $entity->long_description,
            'price' => $entity->price,
            'stock_quantity' => $entity->stock_quantity,
            'brand' => $entity->brand,
            'image' => $entity->image,
        ];

        $sql = $this->buildInsertQuery($fields);
        $stmt = $this->database->prepare($sql);
        $result = $stmt->execute($fields);

        if (!$result) {
            return false;
        }

        $entity->id = (int) $this->database->lastInsertId();

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
            'short_description' => $entity->short_description,
            'long_description' => $entity->long_description,
            'price' => $entity->price,
            'stock_quantity' => $entity->stock_quantity,
            'brand' => $entity->brand,
            'image' => $entity->image,
        ];

        $sql = $this->buildUpdateQuery($fields);
        $stmt = $this->database->prepare($sql);

        $params = array_merge($fields, ['id' => $entity->id]);
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
            INSERT INTO MobileBike (product_id, color, material)
            VALUES (:product_id, :color, :material)
        ";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([
            'product_id' => $mobileBike->id,
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
            WHERE product_id = :product_id
        ";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([
            'product_id' => $mobileBike->id,
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

        $sql = "INSERT INTO {$tableName} (product_id) VALUES (:product_id)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute(['product_id' => $mobileBike->id]);
    }

    private function updateMobileBikeSubType(MobileBike $mobileBike): bool
    {
        // Pour l'instant, les sous-types n'ont pas de champs spécifiques à mettre à jour
        return true;
    }

    private function createSparePart(SparePart $sparePart): bool
    {
        $sql = "INSERT INTO Spare_Part (product_id) VALUES (:product_id)";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute(['product_id' => $sparePart->id]);
    }

    private function updateSparePart(SparePart $sparePart): bool
    {
        // Pour l'instant, SparePart n'a pas de champs spécifiques à mettre à jour
        return true;
    }

    private function deleteMobileBike(int $id): bool
    {
        $sql = "DELETE FROM MobileBike WHERE product_id = :product_id";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute(['product_id' => $id]);
    }

    private function deleteMobileBikeSubType(MobileBike $mobileBike): bool
    {
        $tableName = $this->getMobileBikeSubTypeTable($mobileBike);
        if (!$tableName) {
            return false;
        }

        $sql = "DELETE FROM {$tableName} WHERE product_id = :product_id";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute(['product_id' => $mobileBike->id]);
    }

    private function deleteSparePart(int $id): bool
    {
        $sql = "DELETE FROM Spare_Part WHERE product_id = :product_id";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute(['product_id' => $id]);
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
                return ucfirst($type);
            }
        }
        return null;
    }

    private function hydrateProductsWithType(array $results): array
    {
        $products = [];
        foreach ($results as $row) {
            $type = $this->getProductType($row['id']);
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