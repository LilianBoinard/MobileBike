<?php

namespace MobileBike\App\Model\User;

use DateTime;

class User
{
    public ?int $id;
    public string $username;
    public string $password;
    public string $email;
    public ?DateTime $created;
    public ?string $profile_image;

    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->username = trim($data['username'] ?? '');
        $this->password = $data['password'] ?? '';
        $this->email = trim($data['email'] ?? '');

        // Gestion de la date created
        $this->created = null;
        if (isset($data['created'])) {
            if ($data['created'] instanceof DateTime) {
                $this->created = $data['created'];
            } elseif (is_string($data['created']) && !empty($data['created'])) {
                $this->created = new DateTime($data['created']);
            }
        }

        $this->profile_image = !empty($data['profile_image']) ? trim($data['profile_image']) : null;
    }

    /**
     * Vérifie si l'utilisateur est nouveau (pas encore sauvegardé)
     */
    public function isNew(): bool
    {
        return $this->id === null;
    }

    /**
     * Retourne la date de création formatée
     */
    public function getCreatedFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->created ? $this->created->format($format) : '';
    }

    /**
     * Retourne l'URL complète de l'image de profil ou une image par défaut
     */
    public function getProfileImageUrl(string $defaultImage = 'default-avatar.jpg'): string
    {
        return $this->profile_image ?: $defaultImage;
    }

    /**
     * Vérifie si l'utilisateur a une image de profil
     */
    public function hasProfileImage(): bool
    {
        return !empty($this->profile_image);
    }

    /**
     * Retourne un tableau des données pour la sauvegarde
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'email' => $this->email,
            'created' => $this->created?->format('Y-m-d H:i:s'),
            'profile_image' => $this->profile_image,
        ];
    }

    /**
     * Retourne un tableau des données sans le mot de passe (pour l'affichage)
     */
    public function toSafeArray(): array
    {
        $data = $this->toArray();
        unset($data['password']);
        return $data;
    }
}