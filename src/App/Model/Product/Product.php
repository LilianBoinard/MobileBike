<?php

namespace MobileBike\App\Model\Product;

class Product
{
    public int $id_product;
    public string $name;
    public string $description;
    public float $price;
    public bool $stock;
    public string $brand;
    public string $image;

    public function __construct(array $data = [])
    {
        $this->id_product = $data['id_product'] ?? 0;
        $this->name = trim($data['name']);
        $this->description = trim($data['description'] ?? '');
        $this->price = max(0, round($data['price'] ?? 0, 2));
        $this->stock = (bool)($data['stock'] ?? false);
        $this->brand = $data['brand'] ?? '';
        $this->image = $data['image'] ?? '';
    }

}