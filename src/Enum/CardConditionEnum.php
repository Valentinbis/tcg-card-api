<?php

declare(strict_types=1);

namespace App\Enum;

enum CardConditionEnum: string
{
    case MINT = 'mint';
    case NEAR_MINT = 'near_mint';
    case EXCELLENT = 'excellent';
    case GOOD = 'good';
    case LIGHT_PLAYED = 'light_played';
    case PLAYED = 'played';
    case POOR = 'poor';

    public function labelFr(): string
    {
        return match ($this) {
            self::MINT => 'Neuve',
            self::NEAR_MINT => 'Presque neuve',
            self::EXCELLENT => 'Excellente',
            self::GOOD => 'Bonne',
            self::LIGHT_PLAYED => 'Légèrement jouée',
            self::PLAYED => 'Jouée',
            self::POOR => 'Mauvaise',
        };
    }
}
