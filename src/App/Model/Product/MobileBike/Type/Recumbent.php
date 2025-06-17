<?php

namespace MobileBike\App\Model\Product\MobileBike\Type;

use MobileBike\App\Model\Product\MobileBike\MobileBike;

class Recumbent extends MobileBike
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}