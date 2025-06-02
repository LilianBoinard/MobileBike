<?php

namespace MobileBike\App\Model\Resource;

use MobileBike\App\Model\User\User;

class ResourceItem extends Resource
{
    public User $user;
    public Resource $resource;
}