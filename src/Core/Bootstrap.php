<?php

namespace MobileBike\Core;

use GuzzleHttp\Psr7\ServerRequest;
use HttpSoft\Emitter\SapiEmitter;
use MobileBike\App\Repository\Contracts\UserRepositoryInterface;
use MobileBike\App\Repository\User\UserRepository;
use MobileBike\Core\Authentication\SessionAuthentication;
use MobileBike\Core\Container\Container;
use MobileBike\Core\Contracts\Authentication\AuthenticationInterface;
use MobileBike\Core\Contracts\Session\SessionInterface;
use MobileBike\Core\Database\Database;
use MobileBike\Core\Exception\ExceptionHandler;
use MobileBike\Core\Middleware\AuthenticationMiddleware;
use MobileBike\Core\Middleware\ExceptionMiddleware;
use MobileBike\Core\Exception\Exceptions\NotFoundException;
use MobileBike\Core\Middleware\MiddlewareHandler;
use MobileBike\Core\Routing\RouteLoader;
use MobileBike\Core\Routing\Router;
use MobileBike\Core\Session\NativeSession;
use MobileBike\Core\View\View;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;

/**
 * Classe Bootstrap - Point d'entrée principal du framework MobileBike
 */
class Bootstrap
{
    private Router $router;
    private SapiEmitter $emitter;
    private Container $container;
    private array $globalMiddlewares = []; // Middlewares globaux

    /**
     * Initialise l'application avec tous ses composants
     */
    public static function init(): self
    {
        $app = new self();

        self::loadEnvironment();

        $container = new Container();
        $app->container = $container;

        // Configuration des services
        self::registerCoreServices($container);
        self::registerDatabaseServices($container);
        self::registerExceptionServices($container);

        // Récupération des services
        $app->router = $container->get(Router::class);
        $app->emitter = $container->get(SapiEmitter::class);

        // Configuration des middlewares globaux
        $app->configureGlobalMiddlewares();

        // Chargement des routes
        RouteLoader::loadSafely(__DIR__ . '/../config/routes.php', $app->router);

        return $app;
    }

    /**
     * Configure les middlewares globaux de l'application
     */
    private function configureGlobalMiddlewares(): void
    {
        // Middleware d'exception (doit être le premier pour capturer toutes les erreurs)
        $this->addGlobalMiddleware(ExceptionMiddleware::class);
        // Middleware d'authentification
        $this->addGlobalMiddleware(AuthenticationMiddleware::class);
        // Autres middlewares globaux possibles
        // $this->addGlobalMiddleware(CorsMiddleware::class);
        // $this->addGlobalMiddleware(AuthMiddleware::class);
    }

    /**
     * Ajoute un middleware global
     */
    public function addGlobalMiddleware(string $middlewareClass): void
    {
        $this->globalMiddlewares[] = $middlewareClass;
    }

    /**
     * Enregistre les services liés aux exceptions
     */
    private static function registerExceptionServices(Container $container): void
    {
        // Logger (vous pouvez adapter selon votre implémentation)
        $container->singleton(LoggerInterface::class, function () {
            // Exemple avec Monolog
            $logger = new \Monolog\Logger('mobilebike');
            $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../../var/logs/app.log'));
            return $logger;
        });

        // Twig Environment (si pas déjà configuré)
        $container->singleton(Environment::class, function () {
            $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../App/View');
            return new Environment($loader, [
                'cache' => __DIR__ . '/../../var/cache/twig',
                'debug' => $_ENV['APP_DEBUG'] ?? false,
            ]);
        });

        // ExceptionHandler
        $container->singleton(ExceptionHandler::class, function ($container) {
            return new ExceptionHandler(
                $container->get(LoggerInterface::class),
                $container->get(View::class),
                (bool) ($_ENV['APP_DEBUG'] ?? false)
            );
        });

        // ExceptionMiddleware
        $container->singleton(ExceptionMiddleware::class, function ($container) {
            return new ExceptionMiddleware(
                $container->get(ExceptionHandler::class)
            );
        });
    }

    /**
     * Enregistre les services core du framework
     */
    private static function registerCoreServices(Container $container): void
    {
        $container->singleton(Router::class, function () {
            return new Router();
        });

        $container->singleton(SapiEmitter::class, function () {
            return new SapiEmitter();
        });

        $container->singleton(View::class, function ($container) {
            return new View(
                $container->get(Environment::class)
            );
        });

        // Session
        $container->singleton(SessionInterface::class, function () {
            return new NativeSession();
        });

        // Authentication
        $container->singleton(AuthenticationInterface::class, function ($container) {
            return new SessionAuthentication(
                $container->get(UserRepositoryInterface::class), // Changé ici
                $container->get(SessionInterface::class)
            );
        });

        // AuthenticationMiddleware
        $container->singleton(AuthenticationMiddleware::class, function ($container) {
            return new AuthenticationMiddleware(
                $container->get(AuthenticationInterface::class),
                [
                    'redirectTo' => '/login',
                    'whitelist' => ['login', 'register', 'home']
                ]
            );
        });

        $container->set(ContainerInterface::class, function ($container) {
            return $container;
        });
    }

    /**
     * Enregistre les services liés à la base de données
     */
    private static function registerDatabaseServices(Container $container): void
    {
        $container->singleton(Database::class, function () {
            return new Database();
        });

        $container->set('database', function ($container) {
            return $container->get(Database::class);
        });

        $container->singleton(PDO::class, function ($container) {
            return $container->get(Database::class)->getPdo();
        });

        $container->singleton(UserRepositoryInterface::class, function ($container) {
            return new UserRepository($container->get(Database::class));
        });
    }

    /**
     * Lance l'application
     */
    public function run(): void
    {
        $request = ServerRequest::fromGlobals();

        // 1. Recherche de la route
        $routeMatch = $this->router->match_request($request);

        if ($routeMatch === null) {
            throw new NotFoundException('Route non trouvée');
        }

        $route = $routeMatch->getRoute();
        $params = $routeMatch->getParameters();

        // 2. Injection de la route dans les attributs de la requête
        $request = $request->withAttribute('route', $route);

        // 3. Construction de la chaîne de middlewares
        $middlewareHandler = new MiddlewareHandler();

        // 4. Ajout des middlewares globaux (exception en premier)
        foreach ($this->globalMiddlewares as $globalMiddleware) {
            $middlewareInstance = $this->container->get($globalMiddleware);
            $middlewareHandler->addMiddleware($middlewareInstance);
        }

        // 5. Ajout des middlewares spécifiques à la route
        $routeMiddlewares = method_exists($route, 'getMiddlewares') ? $route->getMiddlewares() : [];
        foreach ($routeMiddlewares as $middleware) {
            $middlewareHandler->addMiddleware($middleware);
        }

        // 6. Configuration du controller final
        $handler = $route->getHandler();
        if (is_string($handler) && str_contains($handler, '@')) {
            [$controllerClass, $method] = explode('@', $handler, 2);

            if (!str_contains($controllerClass, '\\')) {
                $controllerClass = 'MobileBike\\App\\Controller\\' . $controllerClass;
            }

            $controller = $this->resolveController($controllerClass);

            $middlewareHandler->setController(function ($request) use ($controller, $method, $params) {
                return $controller->$method($request, $params);
            });
        } else {
            throw new \InvalidArgumentException('Format de handler non supporté');
        }

        // 7. Exécution de la chaîne complète et émission
        $response = $middlewareHandler->handle($request);
        $this->emitter->emit($response);
    }

    /**
     * Alternative : Lance l'application - VERSION AVEC TRY/CATCH pour compatibilité
     */
    public function runWithFallback(): void
    {
        try {
            $this->run();
        } catch (\Throwable $e) {
            // Fallback si le middleware d'exception n'a pas capturé l'erreur
            $exceptionHandler = $this->container->get(ExceptionHandler::class);
            $request = ServerRequest::fromGlobals();
            $response = $exceptionHandler->handle($e, $request);
            $this->emitter->emit($response);
        }
    }

    /**
     * Résout un controller
     */
    private function resolveController(string $controllerClass): object
    {
        try {
            if ($this->container->has($controllerClass)) {
                return $this->container->get($controllerClass);
            }

            $this->container->set($controllerClass, $controllerClass);
            return $this->container->get($controllerClass);

        } catch (\Exception $e) {
            return new $controllerClass();
        }
    }

    /**
     * Charge les variables d'environnement
     */
    private static function loadEnvironment(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }
}