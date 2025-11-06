<?php

declare(strict_types=1);

namespace App\Attribute;

/**
 * Attribut pour logger automatiquement une action métier.
 *
 * Usage:
 * #[LogAction('user_created', 'User account created')]
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class LogAction
{
    public function __construct(
        public readonly string $action,
        public readonly string $message,
        public readonly string $level = 'info'
    ) {
    }
}
