<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Event Subscriber pour appliquer le rate limiting sur les routes API.
 *
 * Stratégies :
 * - Login/Register : Strict (anti brute-force / spam)
 * - Utilisateurs authentifiés : Permissif (300 req/min)
 * - Utilisateurs anonymes : Modéré (60 req/min)
 */
class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RateLimiterFactory $apiAnonymousLimiter,
        private RateLimiterFactory $apiAuthenticatedLimiter,
        private RateLimiterFactory $apiLoginLimiter,
        private RateLimiterFactory $apiRegisterLimiter,
        private ?Security $security = null,
        private ?LoggerInterface $logger = null
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Appliquer uniquement sur les routes API
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $clientIp = $request->getClientIp() ?? 'unknown';
        $route = $request->attributes->get('_route', 'unknown');

        // Choisir le rate limiter approprié
        $limiter = $this->selectLimiter($route, $clientIp);

        if (!$limiter) {
            return; // Pas de rate limiting pour cette route
        }

        // Créer un identifiant unique pour le rate limiter
        $identifier = $this->getIdentifier($route, $clientIp);

        // Créer le limiter pour cet identifiant et consommer un token
        $limit = $limiter->create($identifier)->consume(1);

        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();

            $this->logger?->warning('Rate limit exceeded', [
                'ip' => $clientIp,
                'route' => $route,
                'identifier' => $identifier,
                'retry_after' => $retryAfter,
                'user' => $this->security?->getUser()?->getUserIdentifier() ?? 'anonymous',
            ]);

            throw new TooManyRequestsHttpException($retryAfter, sprintf('Trop de requêtes. Veuillez réessayer dans %d secondes.', $retryAfter));
        }

        // Ajouter les headers de rate limiting
        $response = $event->getResponse();
        if ($response) {
            $response->headers->set('X-RateLimit-Limit', (string) $limit->getLimit());
            $response->headers->set('X-RateLimit-Remaining', (string) $limit->getRemainingTokens());
            $response->headers->set('X-RateLimit-Reset', (string) $limit->getRetryAfter()->getTimestamp());
        }
    }

    /**
     * Sélectionne le rate limiter approprié selon la route.
     */
    private function selectLimiter(string $route, string $clientIp): ?RateLimiterFactory
    {
        // Rate limiting strict pour login
        if ('api_login' === $route) {
            return $this->apiLoginLimiter;
        }

        // Rate limiting strict pour register
        if ('api_register' === $route) {
            return $this->apiRegisterLimiter;
        }

        // Routes publiques (health check, doc) : pas de rate limiting
        if (in_array($route, ['api_health', 'app.swagger_ui', 'app.swagger'], true)) {
            return null;
        }

        // Utilisateur authentifié : rate limiting permissif
        if ($this->security?->getUser()) {
            return $this->apiAuthenticatedLimiter;
        }

        // Anonyme : rate limiting modéré
        return $this->apiAnonymousLimiter;
    }

    /**
     * Génère un identifiant unique pour le rate limiter.
     */
    private function getIdentifier(string $route, string $clientIp): string
    {
        $user = $this->security?->getUser();

        // Pour login/register : identifiant basé sur IP uniquement
        if (in_array($route, ['api_login', 'api_register'], true)) {
            return sprintf('%s_%s', $route, $clientIp);
        }

        // Pour utilisateur authentifié : basé sur l'ID utilisateur
        if ($user) {
            return sprintf('user_%s', $user->getUserIdentifier());
        }

        // Pour anonyme : basé sur IP
        return sprintf('ip_%s', $clientIp);
    }
}
