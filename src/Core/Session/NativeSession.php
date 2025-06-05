<?php

namespace MobileBike\Core\Session;

use MobileBike\Core\Contracts\Session\SessionInterface;

class NativeSession implements SessionInterface
{
    private bool $started = false;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->start();
        }
    }

    public function start(): void
    {
        if (!$this->started) {
            session_start();
            $this->started = true;
        }
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $_SESSION[$key] : $default;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function remove(string $key): void
    {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public function destroy(): void
    {
        if ($this->started) {
            session_destroy();
            $_SESSION = [];
            $this->started = false;
        }
    }

    public function regenerate(bool $deleteOldSession = true): void
    {
        session_regenerate_id($deleteOldSession);
    }

    public function clear(): void
    {
        $_SESSION = [];
    }
}