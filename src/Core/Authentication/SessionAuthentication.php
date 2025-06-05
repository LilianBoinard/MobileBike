<?php

namespace MobileBike\Core\Authentication;

use MobileBike\App\Repository\User\UserRepository;
use MobileBike\Core\Contracts\Authentication\AuthenticationInterface;
use MobileBike\Core\Contracts\Session\SessionInterface;

class SessionAuthentication implements AuthenticationInterface
{
    protected $repository;
    protected $session;

    public function __construct(
        UserRepository $repository,
        SessionInterface $session
    ) {
        $this->repository = $repository;
        $this->session = $session;
    }

    public function check(): bool
    {
        return $this->session->has('id_user');
    }

    public function user(): ?object
    {
        if (!$this->check()) {
            return null;
        }

        $userId = $this->session->get('id_user');
        return $this->repository->findById($userId);
    }

    public function attempt(array $credentials): bool
    {

        // Priorité à l'email si présent, sinon username
        $user = isset($credentials['email'])
            ? $this->repository->findByEmail($credentials['email'])
            : $this->repository->findByUsername($credentials['username']);
        if ($user && password_verify($credentials['password'], $user->password)) {
            $this->session->set('id_user', $user->id_user);
            return true;
        }

        return false;
    }

    public function logout(): void
    {
        $this->session->remove('id_user');
        $this->session->destroy();
    }
}