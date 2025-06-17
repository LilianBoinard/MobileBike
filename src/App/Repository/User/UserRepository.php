<?php

namespace MobileBike\App\Repository\User;

use MobileBike\App\Model\User\User;
use MobileBike\App\Repository\AbstractRepository;
use MobileBike\App\Repository\Contracts\UserRepositoryInterface;
use MobileBike\Core\Database\Database;

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
        return $this->findOneBy(['username' => $username]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function save(object $entity): bool
    {
        if (!$entity instanceof User) {
            throw new \InvalidArgumentException('L\'entité doit être une instance de User');
        }

        return $this->executeInTransaction(function () use ($entity) {
            if (!$entity->isNew()) {
                // Mise à jour
                $fields = [
                    'username' => $entity->username,
                    'password' => $entity->password,
                    'email' => $entity->email,
                    'profile_image' => $entity->profileImage
                ];

                $sql = $this->buildUpdateQuery($fields);
                $stmt = $this->database->prepare($sql);

                // Ajouter la clé primaire pour la condition WHERE
                $fields[$this->primaryKey] = $entity->id_user;

                return $stmt->execute($fields);
            }

            // Création
            $fields = [
                'username' => $entity->username,
                'password' => password_hash($entity->password, PASSWORD_BCRYPT),
                'email' => $entity->email,
                'profile_image' => $entity->profileImage,
                'created' => date('Y-m-d H:i:s')
            ];

            $sql = $this->buildInsertQuery($fields);
            $stmt = $this->database->prepare($sql);
            $result = $stmt->execute($fields);

            if ($result) {
                $entity->id_user = (int)$this->database->lastInsertId();
            }

            return $result;
        });
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

    /**
     * Vérifie si un nom d'utilisateur est disponible
     */
    public function isUsernameAvailable(string $username): bool
    {
        return $this->available('username', $username);
    }

    /**
     * Vérifie si un email est disponible
     */
    public function isEmailAvailable(string $email): bool
    {
        return $this->available('email', $email);
    }
}