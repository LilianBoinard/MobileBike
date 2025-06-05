<?php

namespace MobileBike\App\Repository\Contracts;

use MobileBike\App\Model\User\User;

interface UserRepositoryInterface
{
    public function findByUsername(string $username): ?User;
    public function findByEmail(string $email): ?User;
    public function save(object $entity): bool;
    public function isClient(int $id): bool;
    public function isAdministrator(int $id): bool;
}