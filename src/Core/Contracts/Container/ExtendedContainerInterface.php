<?php

namespace MobileBike\Core\Contracts\Container;

use Psr\Container\ContainerInterface;

/**
 * Interface ExtendedContainerInterface
 *
 * Étend l'interface PSR-11 pour ajouter les méthodes personnalisées
 * de gestion de services.
 */
interface ExtendedContainerInterface extends ContainerInterface
{
    /**
     * Définit un service dans le conteneur.
     *
     * @param string $id Identifiant du service
     * @param mixed $concrete Définition (closure, nom de classe, valeur)
     * @param bool $shared True si le service doit être partagé (singleton)
     * @return self
     */
    public function set(string $id, mixed $concrete, bool $shared = true): self;

    /**
     * Enregistre un service comme singleton.
     *
     * @param string $id
     * @param mixed $concrete
     * @return self
     */
    public function singleton(string $id, mixed $concrete): self;

    /**
     * Enregistre une instance concrète comme service.
     *
     * @param string $id
     * @param object $instance
     * @return self
     */
    public function instance(string $id, object $instance): self;

    /**
     * Supprime un service du conteneur.
     *
     * @param string $id
     * @return self
     */
    public function remove(string $id): self;
}
