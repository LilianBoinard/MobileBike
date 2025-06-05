<?php

namespace MobileBike\Core\Exception\Exceptions;

use MobileBike\Core\Exception\FrameworkException;

class AuthenticationException extends FrameworkException
{
    protected int $httpStatusCode = 401;
}