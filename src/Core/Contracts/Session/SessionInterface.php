<?php

namespace MobileBike\Core\Contracts\Session;

interface SessionInterface
{
    /**
     * Démarre la session si elle n'est pas déjà active
     */
    public function start(): void;

    /**
     * Vérifie si une clé existe dans la session
     */
    public function has(string $key): bool;

    /**
     * Récupère une valeur de session
     *
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Stocke une valeur en session
     *
     * @param mixed $value
     */
    public function set(string $key, $value): void;

    /**
     * Supprime une clé de la session
     */
    public function remove(string $key): void;

    /**
     * Détruit complètement la session
     */
    public function destroy(): void;

    /**
     * Régénère l'ID de session
     * @param bool $deleteOldSession Supprime l'ancienne session
     */
    public function regenerate(bool $deleteOldSession = true): void;

    /**
     * Vide toutes les données de session tout en conservant la session active
     */
    public function clear(): void;
}