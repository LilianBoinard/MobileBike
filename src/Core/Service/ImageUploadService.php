<?php

namespace MobileBike\Core\Service;

class ImageUploadService
{
    private string $uploadDir;
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private int $maxFileSize = 5 * 1024 * 1024; // 5MB

    public function __construct(string $uploadDir = '/public/uploads/')
    {
        $this->setUploadDir($uploadDir);
    }

    public function setUploadDir(string $uploadDir): void
    {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';

        // Créer le dossier s'il n'existe pas
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                throw new \RuntimeException("Impossible de créer le dossier : " . $this->uploadDir);
            }
        }
    }

    /**
     * Upload une image et retourne le chemin relatif
     */
    public function uploadImage(array $file): ?string
    {
        // Vérifications de base
        if (!$this->isValidFile($file)) {
            return null;
        }

        // Générer un nom unique
        $filename = $this->generateUniqueFilename($file['name']);
        $fullPath = $this->uploadDir . $filename;

        // Déplacer le fichier
        if (copy($file['tmp_name'], $fullPath)) {
            unlink($file['tmp_name']); // Nettoyer le fichier temporaire
            return $filename; // Retourne uniquement le nom du fichier
        }

        return null;
    }

    /**
     * Valide le fichier uploadé
     */
    private function isValidFile(array $file): bool
    {
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Vérifier la taille
        if ($file['size'] > $this->maxFileSize) {
            return false;
        }

        // Vérifier le type MIME
        if (!in_array($file['type'], $this->allowedTypes)) {
            return false;
        }

        // Vérification supplémentaire avec getimagesize
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return false;
        }

        return true;
    }

    /**
     * Génère un nom de fichier unique
     */
    private function generateUniqueFilename(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $timestamp = time();
        $random = bin2hex(random_bytes(8));

        return "product_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Supprime une image
     */
    public function deleteImage(string $imagePath): bool
    {
        $fullPath = $this->uploadDir . basename($imagePath);

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }
}