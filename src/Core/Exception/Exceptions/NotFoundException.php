<?php

namespace MobileBike\Core\Exception\Exceptions;

use MobileBike\Core\Exception\FrameworkException;

class NotFoundException extends FrameworkException
{
    protected int $httpStatusCode = 404;
}