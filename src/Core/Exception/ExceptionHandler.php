<?php

namespace MobileBike\Core\Exception;

use GuzzleHttp\Psr7\Response;
use MobileBike\Core\Exception\Exceptions\ValidationException;
use MobileBike\Core\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Gestionnaire d'exceptions principal
 */
class ExceptionHandler
{
    private LoggerInterface $logger;
    private View $view;
    private bool $debug;
    private array $errorTemplates = [
        400 => 'http/400.html.twig',
        401 => 'http/401.html.twig',
        403 => 'http/403.html.twig',
        404 => 'http/404.html.twig',
        500 => 'http/500.html.twig',
    ];

    public function __construct(
        LoggerInterface $logger,
        View $view,
        bool $debug = false
    ) {
        $this->logger = $logger;
        $this->view = $view;
        $this->debug = $debug;
    }

    /**
     * Gère une exception et retourne une réponse HTTP
     */
    public function handle(\Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        // Log de l'exception
        $this->logException($exception, $request);

        // Détermination du code de statut HTTP
        $statusCode = $this->getStatusCode($exception);

        return $this->createHtmlResponse($exception, $statusCode);
    }

    /**
     * Log l'exception selon son niveau de gravité
     */
    private function logException(\Throwable $exception, ServerRequestInterface $request): void
    {
        $context = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => (string) $request->getUri(),
            'method' => $request->getMethod(),
        ];

        if ($exception instanceof FrameworkException) {
            $context = array_merge($context, $exception->getContext());
        }

        $statusCode = $this->getStatusCode($exception);

        if ($statusCode >= 500) {
            $this->logger->error($exception->getMessage(), $context);
        } elseif ($statusCode >= 400) {
            $this->logger->warning($exception->getMessage(), $context);
        } else {
            $this->logger->info($exception->getMessage(), $context);
        }
    }

    /**
     * Détermine le code de statut HTTP à partir de l'exception
     */
    private function getStatusCode(\Throwable $exception): int
    {
        if ($exception instanceof FrameworkException) {
            return $exception->getHttpStatusCode();
        }

        return 500;
    }

    /**
     * Crée une réponse HTML avec template Twig
     */
    private function createHtmlResponse(\Throwable $exception, int $statusCode): Response
    {
        $template = $this->getErrorTemplate($statusCode);

        $data = [
            'status_code' => $statusCode,
            'message' => $exception->getMessage(),
            'debug' => $this->debug,
        ];

        // En mode debug, ajouter les détails techniques
        if ($this->debug) {
            $data['exception'] = $exception;
            $data['trace'] = $exception->getTraceAsString();
        }

        // Cas spécial pour les erreurs de validation
        if ($exception instanceof ValidationException) {
            $data['errors'] = $exception->getErrors();
        }

        try {
            $html = $this->view->twig($template, $data);
        } catch (\Exception $e) {
            // Fallback en cas d'erreur de template
            $html = $this->view->twig('http/error.html.twig', $data);
        }

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $html
        );
    }

    /**
     * Récupère le template d'erreur approprié
     */
    private function getErrorTemplate(int $statusCode): string
    {
        return $this->errorTemplates[$statusCode] ?? $this->errorTemplates[500];
    }

    /**
     * HTML de fallback en cas d'erreur de template
     */
    private function getFallbackHtml(int $statusCode, string $message): string
    {
        return sprintf(
            '<!DOCTYPE html>
            <html lang="fr">
            <head>
                <title>Erreur %d</title>
                <style>
                    body { font-family: sans-serif; margin: 40px; }
                    .error { background: #f8f8f8; padding: 20px; border-left: 4px solid #e74c3c; }
                </style>
            </head>
            <body>
                <div class="error">
                    <h1>Erreur %d</h1>
                    <p>%s</p>
                </div>
            </body>
            </html>',
            $statusCode,
            $statusCode,
            htmlspecialchars($message)
        );
    }

    /**
     * Configure les templates d'erreur personnalisés
     */
    public function setErrorTemplates(array $templates): void
    {
        $this->errorTemplates = array_merge($this->errorTemplates, $templates);
    }
}