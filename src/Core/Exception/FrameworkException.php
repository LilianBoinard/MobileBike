<?php

namespace MobileBike\Core\Exception;

/**
 * Exception de base du framework
 */
abstract class FrameworkException extends \Exception
{
    protected int $httpStatusCode = 500;
    protected array $context = [];

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }
}