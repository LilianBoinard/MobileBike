<?php

namespace MobileBike\Core\Exception\Exceptions;

use MobileBike\Core\Exception\FrameworkException;

class RouterException extends FrameworkException
{
    protected int $httpStatusCode = 500;
}