<?php

namespace MobileBike\Core\Container;

use MobileBike\Core\Contracts\Container\ExtendedContainerInterface;
use MobileBike\Core\Exception\Exceptions\ContainerException;
use MobileBike\Core\Exception\Exceptions\NotFoundException;


/**
 * Conteneur d'injection de dépendances simple
 * Implémente l'interface PSR-11 ContainerInterface
 */
class Container implements ExtendedContainerInterface
{
    /**
     * @var array Les instances de services déjà créées (singletons)
     */
    private array $instances = [];

    /**
     * @var array Les services marqués comme partagés (singletons)
     */
    private array $shared = [];

    /**
     * @var array Les définitions de services (closures ou classes)
     */
    private array $definitions = [];


    /**
     * Définit un service dans le conteneur
     *
     * @param string $id Identifiant du service
     * @param mixed $concrete La définition (closure ou nom de classe)
     * @param bool $shared Si true, le service sera partagé (singleton)
     * @return self
     */
    public function set(string $id, mixed $concrete, bool $shared = true): self
    {
        $this->definitions[$id] = $concrete;

        if ($shared) {
            $this->shared[$id] = true;
        }

        // Si une instance était déjà créée, on la supprime pour qu'elle soit recréée
        unset($this->instances[$id]);

        return $this;
    }

    /**
     * Vérifie si un service existe dans le conteneur
     *
     * @param string $id Identifiant du service
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->definitions[$id]) || isset($this->instances[$id]);
    }

    /**
     * Récupère un service du conteneur
     *
     * @param string $id Identifiant du service
     * @return mixed
     * @throws NotFoundException Si le service n'existe pas
     * @throws ContainerException Si une erreur se produit lors de la résolution
     */
    public function get(string $id)
    {
        // Si l'instance existe déjà, on la retourne (singleton)
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Si le service n'est pas défini, on lance une exception
        if (!isset($this->definitions[$id])) {
            throw new NotFoundException("Service '$id' not found in container");
        }

        // On récupère la définition
        $concrete = $this->definitions[$id];

        // Si la définition est une closure, on l'exécute
        if ($concrete instanceof \Closure) {
            $instance = $concrete($this);
        } // Si c'est une chaîne (nom de classe), on l'instancie
        elseif (is_string($concrete) && class_exists($concrete)) {
            $instance = $this->resolveClass($concrete);
        } // Sinon c'est une valeur simple
        else {
            $instance = $concrete;
        }

        // Si le service est partagé, on stocke l'instance
        if (isset($this->shared[$id]) && $this->shared[$id]) {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    /**
     * Résout une classe avec ses dépendances par réflexion
     *
     * @param string $className
     * @return object
     * @throws ContainerException|NotFoundException
     */
    private function resolveClass(string $className): object
    {
        try {
            $reflector = new \ReflectionClass($className);

            // Vérifie si la classe est instanciable
            if (!$reflector->isInstantiable()) {
                throw new ContainerException("Class '$className' is not instantiable");
            }

            // Récupère le constructeur
            $constructor = $reflector->getConstructor();

            // S'il n'y a pas de constructeur, on instancie simplement la classe
            if ($constructor === null) {
                return new $className();
            }

            // Récupère les paramètres du constructeur
            $parameters = $constructor->getParameters();

            // Résout chaque paramètre
            $dependencies = [];

            foreach ($parameters as $parameter) {
                // Si le paramètre a un type qui est une classe
                $type = $parameter->getType();

                if ($type && !$type->isBuiltin()) {
                    $typeName = $type->getName();

                    // On essaie de résoudre le type depuis le conteneur
                    if ($this->has($typeName)) {
                        $dependencies[] = $this->get($typeName);
                    } else {
                        // Sinon on crée une nouvelle instance de la classe
                        $dependencies[] = $this->resolveClass($typeName);
                    }
                } // Si le paramètre a une valeur par défaut
                elseif ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } // Si le paramètre est optionnel
                elseif ($parameter->allowsNull()) {
                    $dependencies[] = null;
                } // Sinon impossible de résoudre
                else {
                    throw new ContainerException("Cannot resolve parameter '{$parameter->getName()}' for class '$className'");
                }
            }

            // Instancie la classe avec les dépendances
            return $reflector->newInstanceArgs($dependencies);

        } catch (\ReflectionException $e) {
            throw new ContainerException("Error resolving class '$className': " . $e->getMessage());
        }
    }

    /**
     * Enregistre un service en tant que singleton
     *
     * @param string $id
     * @param mixed $concrete
     * @return self
     */
    public function singleton(string $id, $concrete): self
    {
        return $this->set($id, $concrete, true);
    }

    /**
     * Enregistre un service en tant qu'instance unique
     *
     * @param string $id
     * @param object $instance
     * @return self
     */
    public function instance(string $id, $instance): self
    {
        $this->instances[$id] = $instance;
        return $this;
    }

    /**
     * Supprime un service du conteneur
     *
     * @param string $id
     * @return self
     */
    public function remove(string $id): self
    {
        unset($this->definitions[$id], $this->instances[$id], $this->shared[$id]);
        return $this;
    }
}