<?php

namespace MobileBike\App\Repository\Product;

use MobileBike\App\Model\Product\Product;
use PDO;

class MobileBikeRepository extends ProductRepository
{
    public function findByType(string $type): ?Product {

    }

    public function findByBrand(string $brand): ?Product {
        $sql = "SELECT * FROM mobilebike WHERE brand = :brand";
        $stmt = $this->database->prepare($sql);
        $stmt->execute(['brand' => $brand]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        $product = $stmt->fetch();

        return $product ?: null;
    }

    public function findByColor(string $color): ?Product
    {
        $sql = "SELECT * FROM mobilebike WHERE color = :color";
        $stmt = $this->database->prepare($sql);
        $stmt->execute(['color' => $color]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        $product = $stmt->fetch();

        return $product ?: null;
    }

    public function findByMaterial(string $material): ?Product {
        $sql = "SELECT * FROM mobilebike WHERE material = :material";
        $stmt = $this->database->prepare($sql);
        $stmt->execute(['material' => $material]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        $product = $stmt->fetch();

        return $product ?: null;
    }

}