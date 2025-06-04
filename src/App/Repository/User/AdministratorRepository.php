<?php

namespace MobileBike\App\Repository\User;

use Exception;

class AdministratorRepository extends UserRepository
{

    public function assignAdministrator(int $id_user): bool
    {
        // Vérifie d'abord que l'utilisateur existe
        $stmt = $this->database->prepare("SELECT 1 FROM user_ WHERE id_user = :id_user");
        $stmt->execute(['id_user' => $id_user]);

        if (!$stmt->fetchColumn()) {
            throw new Exception("L'utilisateur avec l'ID $id_user n'existe pas.");
        }

        // Vérifie s'il est déjà client
        if ($this->isAdministrator($id_user)) {
            return false; // Déjà client, on ne fait rien
        }

        // Ajoute dans la table Administrateur
        $stmt = $this->database->prepare("INSERT INTO administrator (id_user) VALUES (:id_user)");
        return $stmt->execute(['id_user' => $id_user]);
    }
}