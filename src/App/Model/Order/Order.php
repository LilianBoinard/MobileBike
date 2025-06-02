<?php

namespace MobileBike\App\Model\Order;

use MobileBike\App\Model\User\User;

class Order
{
    private int $id;
    private int $number;
    private \DateTime $date;
    private User $user;
}