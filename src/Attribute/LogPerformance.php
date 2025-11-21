<?php

declare(strict_types=1);

namespace App\Attribute;

/**
 * Attribut pour logger automatiquement les performances d'une méthode
 * Déclenche un warning si la méthode prend plus de $threshold secondes.
 *
 * Usage:
 * #[LogPerformance(threshold: 0.5)]
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class LogPerformance
{
    public function __construct(
        public readonly float $threshold = 1.0,
        public readonly bool $enabled = true
    ) {
    }
}
