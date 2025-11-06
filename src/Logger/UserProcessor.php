<?php

declare(strict_types=1);

namespace App\Logger;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Ajoute les informations de l'utilisateur connecté aux logs.
 */
class UserProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        try {
            $user = $this->security->getUser();

            if ($user) {
                $record->extra['user'] = [
                    'identifier' => $user->getUserIdentifier(),
                    'roles' => $user->getRoles(),
                ];

                // Ajoute l'ID si c'est une entité User avec getId()
                if (is_object($user) && method_exists($user, 'getId')) {
                    /* @var User $user */
                    $record->extra['user']['id'] = $user->getId();
                }
            } else {
                $record->extra['user'] = [
                    'identifier' => 'anonymous',
                    'roles' => ['ROLE_ANONYMOUS'],
                ];
            }
        } catch (\Throwable $e) {
            $record->extra['user'] = [
                'identifier' => 'error',
                'error' => $e->getMessage(),
            ];
        }

        return $record;
    }
}
