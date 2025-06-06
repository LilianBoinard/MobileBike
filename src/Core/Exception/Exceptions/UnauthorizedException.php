<?php

namespace MobileBike\Core\Exception\Exceptions;

use MobileBike\Core\Exception\FrameworkException;

class UnauthorizedException extends FrameworkException
{
    protected int $httpStatusCode = 401;
}