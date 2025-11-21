<?php

declare(strict_types=1);

namespace App\Attribute;

/**
 * Attribut pour logger automatiquement une action de sécurité.
 *
 * Usage:
 * #[LogSecurity('login_attempt', 'User login attempt')]
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class LogSecurity
{
    public function __construct(
        public readonly string $action,
        public readonly string $message,
        public readonly string $level = 'info'
    ) {
    }
}
