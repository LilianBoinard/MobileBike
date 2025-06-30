<?php

namespace MobileBike\App\Repository\User;

use MobileBike\App\Model\User\User;
use MobileBike\App\Repository\AbstractRepository;
use MobileBike\App\Repository\Contracts\UserRepositoryInterface;
use MobileBike\Core\Database\Database;

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    protected string $table = 'User_';
    protected string $entityClass = User::class;
    protected string $primaryKey = 'id';

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Trouve un utilisateur par nom d'utilisateur
     */
    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    /**
     * Trouve un utilisateur par email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Sauvegarde un utilisateur (création ou mise à jour)
     */
    public function save(object $entity): bool
    {
        if (!$entity instanceof User) {
            throw new \InvalidArgumentException('L\'entité doit être une instance de User');
        }

        return $this->executeInTransaction(function () use ($entity) {
            if ($entity->id) {
                return $this->updateUser($entity);
            } else {
                return $this->createUser($entity);
            }
        });
    }

    /**
     * Assigne un rôle à un utilisateur
     */
    public function assignRole(int $userId, int $roleId): bool
    {
        return $this->executeInTransaction(function () use ($userId, $roleId) {
            $sql = "INSERT IGNORE INTO User_Role (user_id, role_id) VALUES (:user_id, :role_id)";
            $stmt = $this->database->prepare($sql);
            return $stmt->execute([
                'user_id' => $userId,
                'role_id' => $roleId
            ]);
        });
    }

    /**
     * Retire un rôle d'un utilisateur
     */
    public function removeRole(int $userId, int $roleId): bool
    {
        return $this->executeInTransaction(function () use ($userId, $roleId) {
            $sql = "DELETE FROM User_Role WHERE user_id = :user_id AND role_id = :role_id";
            $stmt = $this->database->prepare($sql);
            return $stmt->execute([
                'user_id' => $userId,
                'role_id' => $roleId
            ]);
        });
    }

    /**
     * Vérifie si un utilisateur a un rôle spécifique
     */
    public function hasRole(int $userId, string $roleName): bool
    {
        $sql = "
            SELECT 1 
            FROM User_Role ur
            INNER JOIN Role_ r ON ur.role_id = r.id
            WHERE ur.user_id = :user_id AND r.role_name = :role_name
            LIMIT 1
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'role_name' => $roleName
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Vérifie si un utilisateur est un client
     */
    public function isClient(int $id): bool
    {
        return $this->hasRole($id, 'CLIENT');
    }

    /**
     * Vérifie si un utilisateur est un administrateur
     */
    public function isAdministrator(int $id): bool
    {
        return $this->hasRole($id, 'ADMINISTRATOR');
    }

    /**
     * Récupère tous les rôles d'un utilisateur
     */
    public function getUserRoles(int $userId): array
    {
        $sql = "
            SELECT r.id, r.role_name
            FROM User_Role ur
            INNER JOIN Role_ r ON ur.role_id = r.id
            WHERE ur.user_id = :user_id
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Trouve tous les utilisateurs ayant un rôle spécifique
     */
    public function findByRole(string $roleName): array
    {
        $sql = "
            SELECT u.* 
            FROM {$this->table} u
            INNER JOIN User_Role ur ON u.{$this->primaryKey} = ur.user_id
            INNER JOIN Role_ r ON ur.role_id = r.id
            WHERE r.role_name = :role_name
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['role_name' => $roleName]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->hydrateEntities($results);
    }

    /**
     * Trouve tous les clients
     */
    public function findAllClients(): array
    {
        return $this->findByRole('CLIENT');
    }

    /**
     * Trouve tous les administrateurs
     */
    public function findAllAdministrators(): array
    {
        return $this->findByRole('ADMINISTRATOR');
    }

    /**
     * Trouve les utilisateurs avec leurs rôles
     */
    public function findAllWithRoles(): array
    {
        $sql = "
            SELECT 
                u.*,
                GROUP_CONCAT(r.role_name) as roles
            FROM {$this->table} u
            LEFT JOIN User_Role ur ON u.{$this->primaryKey} = ur.user_id
            LEFT JOIN Role_ r ON ur.role_id = r.id
            GROUP BY u.{$this->primaryKey}
        ";

        $stmt = $this->database->query($sql);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Ajouter les rôles comme propriété supplémentaire
        foreach ($results as &$result) {
            $result['user_roles'] = $result['roles'] ? explode(',', $result['roles']) : [];
            unset($result['roles']);
        }

        return $this->hydrateEntities($results);
    }

    /**
     * Trouve les utilisateurs récents
     */
    public function findRecentUsers(int $days = 7): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE created >= DATE_SUB(NOW(), INTERVAL :days DAY)
            ORDER BY created DESC
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(['days' => $days]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->hydrateEntities($results);
    }

    /**
     * Compte les utilisateurs par rôle
     */
    public function countByRole(): array
    {
        $sql = "
            SELECT 
                r.role_name,
                COUNT(ur.user_id) as count
            FROM Role_ r
            LEFT JOIN User_Role ur ON r.id = ur.role_id
            GROUP BY r.id, r.role_name
        ";

        $stmt = $this->database->query($sql);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Convertir en tableau associatif
        $counts = [];
        foreach ($results as $result) {
            $counts[strtolower($result['role_name'])] = (int) $result['count'];
        }

        return $counts;
    }

    /**
     * Vérifie si un nom d'utilisateur est disponible
     */
    public function isUsernameAvailable(string $username, ?int $excludeUserId = null): bool
    {
        return $this->available('username', $username, $excludeUserId);
    }

    /**
     * Vérifie si un email est disponible
     */
    public function isEmailAvailable(string $email, ?int $excludeUserId = null): bool
    {
        return $this->available('email', $email, $excludeUserId);
    }

    /**
     * Supprime un utilisateur avec ses rôles
     */
    public function delete(int $id): bool
    {
        return $this->executeInTransaction(function () use ($id) {
            // Les rôles seront supprimés automatiquement grâce à CASCADE
            return parent::delete($id);
        });
    }

    // === MÉTHODES PRIVÉES ===

    /**
     * Crée un nouvel utilisateur
     */
    private function createUser(User $user): bool
    {
        $fields = [
            'username' => $user->username,
            'password' => password_hash($user->password, PASSWORD_BCRYPT),
            'email' => $user->email,
            'profile_image' => $user->profile_image,
            'created' => date('Y-m-d H:i:s')
        ];

        $sql = $this->buildInsertQuery($fields);
        $stmt = $this->database->prepare($sql);
        $result = $stmt->execute($fields);

        if ($result) {
            $user->id = (int) $this->database->lastInsertId();
        }

        return $result;
    }

    /**
     * Met à jour un utilisateur existant
     */
    private function updateUser(User $user): bool
    {
        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        $existingUser = $this->findByEmail($user->email);
        if ($existingUser && $existingUser->id !== $user->id) {
            throw new \InvalidArgumentException("Cet email est déjà utilisé par un autre utilisateur");
        }

        // Vérifier si le nom d'utilisateur est déjà utilisé par un autre utilisateur
        $existingUserByUsername = $this->findByUsername($user->username);
        if ($existingUserByUsername && $existingUserByUsername->id !== $user->id) {
            throw new \InvalidArgumentException("Ce nom d'utilisateur est déjà utilisé par un autre utilisateur");
        }

        $fields = [
            'username' => $user->username,
            'email' => $user->email,
            'profile_image' => $user->profile_image
        ];

        // Ne hash le mot de passe que s'il a changé
        if (!empty($user->password) && !password_verify('', $user->password)) {
            $fields['password'] = password_hash($user->password, PASSWORD_BCRYPT);
        }

        $sql = $this->buildUpdateQuery($fields);
        $stmt = $this->database->prepare($sql);

        // Ajouter la clé primaire pour la condition WHERE
        $params = array_merge($fields, [$this->primaryKey => $user->id]);

        return $stmt->execute($params);
    }
}