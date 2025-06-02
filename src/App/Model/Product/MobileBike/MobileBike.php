<?php

namespace MobileBike\App\Model\Product\MobileBike;

use MobileBike\App\Model\Product\Product;

class MobileBike extends Product
{
    private string $image;
    private ?string $color = null;
    private ?string $material = null;
    private string $brand;
}