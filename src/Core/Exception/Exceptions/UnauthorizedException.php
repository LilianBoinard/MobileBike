<?php

namespace MobileBike\Core\Exception\Exceptions;

use MobileBike\Core\Exception\FrameworkException;

class UnauthorizedException extends FrameworkException
{
    protected $message = 'Unauthorized';
    protected int $httpStatusCode = 401;
}