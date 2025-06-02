<?php

namespace MobileBike\App\Repository;

use MobileBike\App\Model\User\User;
use MobileBike\Core\Database\Database;
use PDO;

class UserRepository extends AbstractRepository
{
    protected string $table = 'users';
    protected string $entityClass = User::class;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->database->prepare("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function save(object $entity): bool {

        if (!$entity instanceof User) {
            throw new \InvalidArgumentException('L\'entité doit être une instance de User');
        }

        if ($entity->id) {
            // Mise à jour
            $stmt = $this->database->prepare("
                        UPDATE {$this->table} 
                        SET username = :username, password = :passowrd, email = :email
                        WHERE id = :id
                        ");
            return $stmt->execute([
                'id' => $entity->id,
                'username' => $entity->username,
                'password' => password_hash($entity->passowrd, PASSWORD_BCRYPT),
                'email' => $entity->email,
            ]);
        }

        // Creation
        $stmt = $this->database->prepare("
        INSERT INTO {$this->table} (username, password, email, created) 
                VALUES (:username, :password, :email, NOW())
                ");
        $result = $stmt->execute([
            'username' => $entity->username,
            'password' => password_hash($entity->passowrd, PASSWORD_BCRYPT),
            'email' => $entity->email,
        ]);

        if ($result) {
            $entity->id = (int) $this->database->lastInsertId();
        }

        return $result;
    }

}