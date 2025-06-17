<?php

namespace MobileBike\Core\Exception\Exceptions;

use MobileBike\Core\Exception\FrameworkException;

class ImageUploadException extends FrameworkException
{
    protected int $httpStatusCode = 500;

    public function __construct(string $message = "Image upload failed")
    {
        parent::__construct($message);
    }
}