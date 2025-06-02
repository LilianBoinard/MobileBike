<?php

namespace MobileBike\App\Model\Product\MobileBike;

use MobileBike\App\Model\Product\Product;

class MobileBike extends Product
{
    public string $image;
    public ?string $color = null;
    public ?string $material = null;
    public string $brand;
}