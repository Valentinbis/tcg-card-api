<?php

namespace App\Logger;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Ajoute les informations de la requête HTTP aux logs
 */
class RequestProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if ($request) {
            $record->extra['request'] = [
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
            ];
            
            // Ajoute l'ID de requête unique si disponible
            if ($request->headers->has('X-Request-ID')) {
                $record->extra['request']['id'] = $request->headers->get('X-Request-ID');
            }
        }

        return $record;
    }
}
