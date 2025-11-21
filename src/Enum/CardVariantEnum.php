<?php

declare(strict_types=1);

namespace App\Enum;

enum CardVariantEnum: string
{
    case NORMAL = 'normal';
    case REVERSE = 'reverse';
    case HOLO = 'holo';

    public function labelFr(): string
    {
        return match ($this) {
            self::NORMAL => 'Normale',
            self::REVERSE => 'Reverse',
            self::HOLO => 'Holo',
        };
    }
}
