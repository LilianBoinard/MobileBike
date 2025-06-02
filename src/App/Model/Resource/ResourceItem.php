<?php

namespace MobileBike\App\Model\Resource;

use MobileBike\App\Model\User\User;

class ResourceItem extends Resource
{
    private User $user;
    private Resource $resource;
}