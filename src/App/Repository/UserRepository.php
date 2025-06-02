<?php

namespace MobileBike\App\Repository;

use MobileBike\App\Model\User;
use MobileBike\Core\Database\Database;
use PDO;

class UserRepository
{
    private Database $database;
    protected string $table = 'users';
    protected string $entityClass = User::class;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function findByLogin(string $login): ?User
    {
        $stmt = $this->database->prepare("SELECT * FROM {$this->table} WHERE login = :login LIMIT 1");
        $stmt->execute(['login' => $login]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        $user = $stmt->fetch();
        return $user ?: null;
    }

}