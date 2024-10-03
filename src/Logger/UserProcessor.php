<?php

namespace App\Logger;

use App\Security\User;
use Symfony\Bundle\SecurityBundle\Security;
use Monolog\LogRecord;

/**
 * Add login name in logger
 */
class UserProcessor
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    // this method is called for each log record; optimize it to not hurt performance
    public function __invoke(LogRecord $record): LogRecord
    {
        try {
            /** @var User $user */
            $user = $this->security->getUser();
            $record->extra['user'] = $user ? $user->getUserIdentifier() : 'anonymous';
        } catch (\Throwable $e) {
            $record->extra['user'] = 'anonymous';
        }

        return $record;
    }
}