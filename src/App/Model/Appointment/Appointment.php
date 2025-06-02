<?php

namespace MobileBike\App\Model\Appointment;

use MobileBike\App\Model\User\User;

class Appointment
{
    private int $id;
    private string $address;
    private \DateTime $date;
    private string $message;
    private User $user;
}