<?php

namespace MobileBike\App\Repository\User;

use MobileBike\App\Model\User\User;
use MobileBike\App\Repository\AbstractRepository;
use MobileBike\App\Repository\Contracts\UserRepositoryInterface;
use MobileBike\Core\Database\Database;
use PDO;

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    protected string $table = 'user_';
    protected string $entityClass = User::class;
    protected string $primaryKey = 'id_user';

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function findByUsername(string $username): ?User
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['username' => $username]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['email' => $email]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function save(object $entity): bool
    {

        if (!$entity instanceof User) {
            throw new \InvalidArgumentException('L\'entité doit être une instance de User');
        }

        if ($entity->id) {

            // Mise à jour

            $sql = "
                        UPDATE {$this->table} 
                        SET username = :username, password = :passowrd, email = :email
                        WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            return $stmt->execute([
                'id' => $entity->id,
                'username' => $entity->username,
                'password' => password_hash($entity->password, PASSWORD_BCRYPT),
                'email' => $entity->email,
            ]);
        }

        // Creation

        $sql = "
                    INSERT INTO {$this->table} (username, password, email, created) 
                    VALUES (:username, :password, :email, NOW())";

        $stmt = $this->database->prepare($sql);
        $result = $stmt->execute([
            'username' => $entity->username,
            'password' => password_hash($entity->password, PASSWORD_BCRYPT),
            'email' => $entity->email,
        ]);

        if ($result) {
            $entity->id = (int)$this->database->lastInsertId();
        }

        return $result;
    }

    public function isClient(int $id): bool
    {
        $sql = "SELECT 1 FROM client WHERE id_user = :id_user LIMIT 1";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['id_user' => $id]);

        return (bool)$stmt->fetchColumn();
    }

    public function isAdministrator(int $id): bool
    {
        $sql = "SELECT 1 FROM administrator WHERE id_user = :id_user LIMIT 1";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['id_user' => $id]);

        return (bool)$stmt->fetchColumn();
    }

}