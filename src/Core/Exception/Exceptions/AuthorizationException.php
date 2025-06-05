<?php

namespace MobileBike\Core\Exception\Exceptions;

use MobileBike\Core\Exception\FrameworkException;

class AuthorizationException extends FrameworkException
{
    protected int $httpStatusCode = 403;
}