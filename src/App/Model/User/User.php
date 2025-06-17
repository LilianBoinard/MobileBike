<?php

namespace MobileBike\App\Model\User;

class User
{
    public ?int $id_user = null;
    public string $username = '';
    public string $password = '';
    public string $email = '';
    public string $created = '';
    public string $profileImage = '';

    /**
     * Constructeur qui accepte un tableau de données (pour hydrateEntity)
     * ou aucun paramètre (pour PDO::FETCH_CLASS)
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Hydrate l'objet avec un tableau de données
     */
    private function hydrate(array $data): void
    {
        $this->id_user = isset($data['id_user']) ? (int)$data['id_user'] : null;
        $this->username = $data['username'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->created = $data['created'] ?? '';
        $this->profileImage = $data['profile_image'] ?? '';
    }

    /**
     * Magic setter pour gérer les noms de colonnes de la DB
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'profile_image':
                $this->profileImage = $value;
                break;
            case 'id_user':
                $this->id_user = $value ? (int)$value : null;
                break;
            default:
                if (property_exists($this, $name)) {
                    $this->$name = $value;
                }
                break;
        }
    }

    /**
     * Magic getter pour la compatibilité
     */
    public function __get($name)
    {
        switch ($name) {
            case 'profile_image':
                return $this->profileImage;
            default:
                return property_exists($this, $name) ? $this->$name : null;
        }
    }

    /**
     * Vérifie si l'utilisateur est nouveau (pas encore sauvegardé)
     */
    public function isNew(): bool
    {
        return $this->id_user === null;
    }

    /**
     * Retourne un tableau des données pour la sauvegarde
     */
    public function toArray(): array
    {
        return [
            'id_user' => $this->id_user,
            'username' => $this->username,
            'password' => $this->password,
            'email' => $this->email,
            'created' => $this->created,
            'profile_image' => $this->profileImage,
        ];
    }
}