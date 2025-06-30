<?php

namespace MobileBike\App\Model\Product;

class Product
{
    public int $id;
    public string $name;
    public string $short_description;
    public string $long_description;
    public float $price;
    public int $stock_quantity;
    public string $brand;
    public string $image;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->name = trim($data['name'] ?? '');
        $this->short_description = trim($data['short_description'] ?? '');
        $this->long_description = trim($data['long_description'] ?? '');
        $this->price = max(0, round($data['price'] ?? 0, 2));
        $this->stock_quantity = max(0, (int)($data['stock_quantity'] ?? 0));
        $this->brand = trim($data['brand'] ?? '');
        $this->image = trim($data['image'] ?? '');
    }

    /**
     * Vérifie si le produit est en stock
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Vérifie si une quantité donnée est disponible en stock
     */
    public function hasStock(int $quantity): bool
    {
        return $this->stock_quantity >= $quantity;
    }
}