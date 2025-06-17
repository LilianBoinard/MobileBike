<?php

namespace MobileBike\App\Model\Product\MobileBike;

use MobileBike\App\Model\Product\Product;

class MobileBike extends Product
{
    public string $brand;
    public string $image;
    public ?string $color;
    public ?string $material;

    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->brand = $data['brand'] ?? '';
        $this->image = $data['image'] ?? '';
        $this->color = $data['color'] ?? null;
        $this->material = $data['material'] ?? null;
    }
}
