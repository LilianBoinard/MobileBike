<?php

namespace MobileBike\Core;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use HttpSoft\Emitter\SapiEmitter;
use MobileBike\Core\Container\Container;
use MobileBike\Core\Database\Database;
use MobileBike\Core\Exception\NotFoundException;
use MobileBike\Core\Http\Middleware\MiddlewareHandler;
use MobileBike\Core\Routing\RouteLoader;
use MobileBike\Core\Routing\Router;
use PDO;
use Psr\Container\ContainerInterface;

/**
 * Classe Bootstrap - Point d'entrée principal du framework MobileBike
 *
 * Responsable de l'initialisation complète de l'application :
 * - Configuration du container d'injection de dépendances
 * - Enregistrement des services core et database
 * - Gestion du routage et des middlewares
 * - Traitement des requêtes HTTP et gestion des erreurs
 */
class Bootstrap
{
    private Router $router;
    private SapiEmitter $emitter;
    private Container $container;

    /**
     * Initialise l'application avec tous ses composants
     *
     * @return self Instance configurée de Bootstrap
     */
    public static function init(): self
    {
        // Auto instantiation
        $app = new self();

        // Création d'une Session
        session_start();

        // Chargement des variables d'environnement
        self::loadEnvironment();

        // Creation du container
        $container = new Container();
        $app->container = $container;

        // Configuration des services core
        self::registerCoreServices($container);

        // Configuration de la base de données
        self::registerDatabaseServices($container);

        // CONTAINER GET
        $app->router = $container->get(Router::class);
        $app->emitter = $container->get(SapiEmitter::class);

        // Charger les routes
        RouteLoader::loadSafely(__DIR__ . '/../config/routes.php', $app->router);

        return $app;
    }

    /**
     * Enregistre les services core du framework
     *
     * @param Container $container Container d'injection de dépendances
     */
    private static function registerCoreServices(Container $container): void
    {
        // Router - Singleton pour une instance unique
        $container->singleton(Router::class, function () {
            return new Router();
        });

        // Emitter - Pour l'émission des réponses HTTP
        $container->singleton(SapiEmitter::class, function () {
            return new SapiEmitter();
        });

        // Container lui-même pour injection dans d'autres services
        $container->set(ContainerInterface::class, function ($container) {
            return $container;
        });
    }

    /**
     * Enregistre les services liés à la base de données
     *
     * @param Container $container Container d'injection de dépendances
     */
    private static function registerDatabaseServices(Container $container): void
    {
        // Configuration Database avec les variables d'environnement
        $container->singleton(Database::class, function () {
            $config = [
                'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'database' => $_ENV['DB_NAME'] ?? '',
                'username' => $_ENV['DB_USER'] ?? '',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
                'port' => $_ENV['DB_PORT'] ?? 3306,
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => filter_var($_ENV['DB_PERSISTENT'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . ($_ENV['DB_CHARSET'] ?? 'utf8mb4')
                ]
            ];

            return new Database($config);
        });

        // Alias 'db' pour un accès plus simple
        $container->set('db', function ($container) {
            return $container->get(Database::class);
        });

        // PDO direct si nécessaire pour des requêtes spécifiques
        $container->singleton(PDO::class, function ($container) {
            return $container->get(Database::class)->getPdo();
        });
    }

    /**
     * Retourne le container pour accès externe si nécessaire
     *
     * @return Container Instance du container d'injection de dépendances
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Lance l'application et traite la requête HTTP entrante
     *
     * Processus :
     * 1. Création de la requête depuis les globals PHP
     * 2. Matching de la route correspondante
     * 3. Résolution du controller et exécution via les middlewares
     * 4. Émission de la réponse HTTP
     * 5. Gestion des erreurs (404, 500)
     */
    public function run(): void
    {
        $request = ServerRequest::fromGlobals();

        try {
            // Recherche de la route correspondant à la requête
            $routeMatch = $this->router->match_request($request);

            if ($routeMatch === null) {
                throw new NotFoundException('Route non trouvée');
            }

            $route = $routeMatch->getRoute();
            $params = $routeMatch->getParameters();

            // Récupération des middlewares de la route si disponibles
            $middlewares = method_exists($route, 'getMiddlewares') ? $route->getMiddlewares() : [];

            // Configuration de la chaîne de middlewares
            $middlewareHandler = new MiddlewareHandler();
            foreach ($middlewares as $middleware) {
                $middlewareHandler->addMiddleware($middleware);
            }

            // Analyse du handler au format "Controller@method"
            $handler = $route->getHandler();
            if (is_string($handler) && str_contains($handler, '@')) {
                [$controllerClass, $method] = explode('@', $handler, 2);

                // Ajout du namespace complet si nécessaire
                if (!str_contains($controllerClass, '\\')) {
                    $controllerClass = 'MobileBike\\App\\Controller\\' . $controllerClass;
                }

                // Résolution du controller via le container pour l'injection de dépendances
                $controller = $this->resolveController($controllerClass);

                // Configuration du controller final dans la chaîne de middlewares
                $middlewareHandler->setController(function ($request) use ($controller, $method, $params) {
                    return $controller->$method($request, $params);
                });
            } else {
                throw new \InvalidArgumentException('Format de handler non supporté');
            }

            // Exécution de la chaîne de middlewares et émission de la réponse
            $response = $middlewareHandler->handle($request);
            $this->emitter->emit($response);

        } catch (NotFoundException $e) {
            // Gestion des erreurs 404
            $response = new Response(
                404,
                ['Content-Type' => 'text/html'],
                '<h1>404 - Page non trouvée</h1>'
            );
            $this->emitter->emit($response);
        } catch (\Exception $e) {
            // Gestion des erreurs générales (500)
            $response = new Response(
                500,
                ['Content-Type' => 'text/html'],
                '<h1>500 - Erreur interne du serveur</h1>'
            );
            $this->emitter->emit($response);

            // Log de l'erreur pour le debugging
            error_log($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    /**
     * Résout un controller en utilisant le container pour l'injection de dépendances
     *
     * Stratégie de résolution :
     * 1. Tentative via le container si déjà enregistré
     * 2. Enregistrement automatique puis résolution
     * 3. Fallback : instanciation manuelle
     *
     * @param string $controllerClass Nom complet de la classe du controller
     * @return object Instance du controller résolu
     */
    private function resolveController(string $controllerClass): object
    {
        try {
            // Vérification si le controller est déjà enregistré dans le container
            if ($this->container->has($controllerClass)) {
                return $this->container->get($controllerClass);
            }

            // Enregistrement automatique puis résolution
            $this->container->set($controllerClass, $controllerClass);
            return $this->container->get($controllerClass);

        } catch (\Exception $e) {
            // Solution de repli : instanciation manuelle sans injection
            return new $controllerClass();
        }
    }

    /**
     * Charge les variables d'environnement depuis le fichier .env
     */
    private static function loadEnvironment(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }
}