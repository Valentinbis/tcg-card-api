<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Service centralisé pour la gestion des logs avec contexte enrichi
 */
class LoggerService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LoggerInterface $actionLogger,
        private readonly LoggerInterface $securityLogger,
        private readonly LoggerInterface $requestLogger,
        private readonly LoggerInterface $performanceLogger,
        private readonly RequestStack $requestStack,
        private readonly TokenStorageInterface $tokenStorage,
    ) {}

    /**
     * Log une action métier
     */
    public function logAction(string $message, array $context = [], string $level = 'info'): void
    {
        $enrichedContext = $this->enrichContext($context);
        $this->actionLogger->log($level, $message, $enrichedContext);
    }

    /**
     * Log un événement de sécurité
     */
    public function logSecurity(string $message, array $context = [], string $level = 'info'): void
    {
        $enrichedContext = $this->enrichContext($context);
        $this->securityLogger->log($level, $message, $enrichedContext);
    }

    /**
     * Log une requête HTTP
     */
    public function logRequest(string $message, array $context = [], string $level = 'info'): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $context['http'] = [
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'status' => $context['status'] ?? null,
                'duration' => $context['duration'] ?? null,
            ];
        }
        $this->requestLogger->log($level, $message, $context);
    }

    /**
     * Log des métriques de performance
     */
    public function logPerformance(string $operation, float $duration, array $context = []): void
    {
        $context['duration_ms'] = round($duration * 1000, 2);
        $context['operation'] = $operation;
        
        $level = 'info';
        if ($duration > 1.0) {
            $level = 'warning';
        } elseif ($duration > 5.0) {
            $level = 'error';
        }
        
        $this->performanceLogger->log($level, "Operation completed: {$operation}", $context);
    }

    /**
     * Log une erreur avec contexte complet
     */
    public function logError(\Throwable $exception, array $context = []): void
    {
        $context['exception'] = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];
        
        $enrichedContext = $this->enrichContext($context);
        $this->logger->error($exception->getMessage(), $enrichedContext);
    }

    /**
     * Log générique avec contexte enrichi
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $enrichedContext = $this->enrichContext($context);
        $this->logger->log($level, $message, $enrichedContext);
    }

    /**
     * Enrichit le contexte avec des informations additionnelles
     */
    private function enrichContext(array $context): array
    {
        // Ajoute un timestamp
        $context['timestamp'] = (new \DateTime())->format(\DateTime::ATOM);
        
        // Ajoute l'environnement
        $context['environment'] = $_ENV['APP_ENV'] ?? 'unknown';
        
        // Ajoute un ID de corrélation unique pour tracer les requêtes
        $request = $this->requestStack->getCurrentRequest();
        if ($request && !isset($context['correlation_id'])) {
            $context['correlation_id'] = $request->attributes->get('correlation_id') 
                ?? uniqid('req_', true);
        }
        
        return $context;
    }

    /**
     * Helpers pour les niveaux de log courants
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }
}
