<?php

namespace MobileBike\Core\Exception\Exceptions;

use MobileBike\Core\Exception\FrameworkException;

class ValidationException extends FrameworkException
{
    protected int $httpStatusCode = 400;
    protected array $errors = [];

    public function __construct(array $errors, string $message = "Validation failed")
    {
        $this->errors = $errors;
        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}