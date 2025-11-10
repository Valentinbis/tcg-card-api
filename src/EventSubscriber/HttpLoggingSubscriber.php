<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Attribute\LogAction;
use App\Attribute\LogPerformance;
use App\Attribute\LogSecurity;
use App\Service\LoggerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Log automatiquement les requêtes HTTP, réponses et exceptions
 * Gère également le logging déclaratif via attributs PHP.
 */
class HttpLoggingSubscriber implements EventSubscriberInterface
{
    /** @var array<string, float> */
    private array $requestStartTimes = [];
    
    /** @var array<string, array<string, mixed>> */
    private array $controllerAttributes = [];

    public function __construct(
        private readonly LoggerService $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
            KernelEvents::CONTROLLER => ['onKernelController', 0],
            KernelEvents::RESPONSE => ['onKernelResponse', -1000],
            KernelEvents::EXCEPTION => ['onKernelException', 0],
            KernelEvents::TERMINATE => ['onKernelTerminate', -1024],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Génère un ID unique de corrélation pour tracer la requête
        $correlationId = uniqid('req_', true);
        $request->attributes->set('correlation_id', $correlationId);

        // Stocke le temps de début pour calculer la durée
        $this->requestStartTimes[$correlationId] = microtime(true);

        // Log la requête entrante (silencieux pour ne pas polluer)
        if (!$this->isHealthCheck($request)) {
            $this->logger->logRequest('Incoming request', [
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'ip' => $request->getClientIp(),
                'correlation_id' => $correlationId,
            ]);
        }
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $controller = $event->getController();
        $request = $event->getRequest();
        $correlationId = $request->attributes->get('correlation_id');
        assert(is_string($correlationId));

        // Extraire la méthode du contrôleur
        if (is_array($controller) && is_object($controller[0]) && is_string($controller[1])) {
            $method = new \ReflectionMethod($controller[0], $controller[1]);

            // Récupérer les attributs de logging
            $attributes = [
                'action' => $method->getAttributes(LogAction::class),
                'security' => $method->getAttributes(LogSecurity::class),
                'performance' => $method->getAttributes(LogPerformance::class),
            ];

            // Stocker pour utilisation dans onKernelTerminate
            $this->controllerAttributes[$correlationId] = [
                'method' => $method,
                'attributes' => $attributes,
                'controller' => get_class($controller[0]),
                'action' => $controller[1],
            ];
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        $correlationId = $request->attributes->get('correlation_id');
        assert(is_string($correlationId));

        $duration = isset($this->requestStartTimes[$correlationId])
            ? microtime(true) - $this->requestStartTimes[$correlationId]
            : 0;

        // Ne log que les requêtes importantes (pas les health checks)
        if (!$this->isHealthCheck($request)) {
            // Détermine le niveau de log en fonction du status code
            $level = 'info';
            if ($response->getStatusCode() >= 500) {
                $level = 'error';
            } elseif ($response->getStatusCode() >= 400) {
                $level = 'warning';
            }

            $this->logger->logRequest('Request completed', [
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'status' => $response->getStatusCode(),
                'duration' => round($duration * 1000, 2).'ms',
                'correlation_id' => $correlationId,
            ], $level);
        }

        // Log les performances si la requête est lente
        if ($duration > 0.5 && !$this->isHealthCheck($request)) {
            $this->logger->logPerformance('slow_request', $duration, [
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'status' => $response->getStatusCode(),
            ]);
        }
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $exception = $event->getThrowable();
        $correlationId = $request->attributes->get('correlation_id');

        $this->logger->logError($exception, [
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'correlation_id' => $correlationId,
        ]);
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $correlationId = $request->attributes->get('correlation_id');
        
        if (!is_string($correlationId)) {
            return;
        }

        // Traiter les logs déclaratifs via attributs
        if (isset($this->controllerAttributes[$correlationId])) {
            $data = $this->controllerAttributes[$correlationId];
            $duration = isset($this->requestStartTimes[$correlationId])
                ? microtime(true) - $this->requestStartTimes[$correlationId]
                : 0;

            $context = [
                'controller' => $data['controller'] ?? 'unknown',
                'action' => $data['action'] ?? 'unknown',
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'status' => $response->getStatusCode(),
                'correlation_id' => $correlationId,
            ];

            // Log Action
            $attributes = $data['attributes'] ?? null;
            if (isset($attributes['action']) && is_array($attributes) && is_array($attributes['action'])) {
                foreach ($attributes['action'] as $attr) {
                    if (!is_object($attr) || !method_exists($attr, 'newInstance')) {
                        continue;
                    }
                    /** @var LogAction $instance */
                    $instance = $attr->newInstance();
                    $context['action_type'] = $instance->action;

                    $this->logger->logAction(
                        $instance->message,
                        $context,
                        $instance->level
                    );
                }
            }

            // Log Security
            if (isset($attributes['security']) && is_array($attributes) && is_array($attributes['security'])) {
                foreach ($attributes['security'] as $attr) {
                    if (!is_object($attr) || !method_exists($attr, 'newInstance')) {
                        continue;
                    }
                    /** @var LogSecurity $instance */
                    $instance = $attr->newInstance();
                    $context['security_action'] = $instance->action;

                    $this->logger->logSecurity(
                        $instance->message,
                        $context,
                        $instance->level
                    );
                }
            }

            // Log Performance
            if (isset($attributes['performance']) && is_array($attributes) && is_array($attributes['performance'])) {
                foreach ($attributes['performance'] as $attr) {
                    if (!is_object($attr) || !method_exists($attr, 'newInstance')) {
                        continue;
                    }
                    /** @var LogPerformance $instance */
                    $instance = $attr->newInstance();

                    if ($instance->enabled) {
                        $actionName = is_string($data['action']) ? $data['action'] : 'unknown';
                        $this->logger->logPerformance(
                            $actionName,
                            $duration,
                            array_merge($context, [
                                'threshold' => $instance->threshold,
                                'exceeded' => $duration > $instance->threshold,
                            ])
                        );

                        // Warning si le seuil est dépassé
                        if ($duration > $instance->threshold) {
                            $this->logger->warning(
                                'Performance threshold exceeded',
                                array_merge($context, [
                                    'duration_seconds' => $duration,
                                    'threshold_seconds' => $instance->threshold,
                                ])
                            );
                        }
                    }
                }
            }
        }

        // Nettoyage
        if ($correlationId) {
            unset($this->requestStartTimes[$correlationId]);
            unset($this->controllerAttributes[$correlationId]);
        }
    }

    /**
     * Vérifie si c'est un health check pour éviter de polluer les logs.
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function isHealthCheck($request): bool
    {
        if (!is_object($request) || !method_exists($request, 'getRequestUri')) {
            return false;
        }
        
        $uri = $request->getRequestUri();

        return '/api' === $uri || '/health' === $uri || '/ping' === $uri;
    }
}
