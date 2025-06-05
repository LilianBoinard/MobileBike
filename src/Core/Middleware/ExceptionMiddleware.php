<?php

namespace MobileBike\Core\Middleware;

use MobileBike\Core\Contracts\Middleware\MiddlewareInterface;
use MobileBike\Core\Exception\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware pour capturer les exceptions
 */
class ExceptionMiddleware implements MiddlewareInterface
{
    private ExceptionHandler $handler;

    public function __construct(ExceptionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        try {
            return $next($request);
        } catch (\Throwable $exception) {
            return $this->handler->handle($exception, $request);
        }
    }
}