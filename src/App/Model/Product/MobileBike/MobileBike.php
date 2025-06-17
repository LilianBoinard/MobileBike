<?php

namespace MobileBike\App\Model\Product\MobileBike;

use MobileBike\App\Model\Product\Product;

class MobileBike extends Product
{
    public ?string $color;
    public ?string $material;

    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->color = $data['color'] ?? null;
        $this->material = $data['material'] ?? null;
    }
}
