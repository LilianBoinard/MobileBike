<?php

namespace MobileBike\Core\Contracts\Authentication;

interface AuthenticationInterface
{
    public function check(): bool;
    public function user(): ?object;
    public function attempt(array $credentials): bool;
    public function logout(): void;
}