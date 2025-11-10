<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Gère automatiquement les requêtes OPTIONS (CORS preflight) pour toutes les routes API.
 */
class CorsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9999], // Priorité très haute
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Ne traiter que les requêtes OPTIONS sur les routes /api/*
        if ($request->getMethod() !== 'OPTIONS' || !str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        // Répondre immédiatement avec 204 No Content pour le preflight CORS
        $response = new Response('', Response::HTTP_NO_CONTENT);
        
        // Nelmio CORS ajoutera automatiquement les headers CORS nécessaires
        $event->setResponse($response);
    }
}
