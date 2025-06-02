<?php

namespace MobileBike\App\Model\Order;

use MobileBike\App\Model\Product\Product;

class OrderItem
{
    private Product $product;
    private Order $order;
    private int $quantity;
}