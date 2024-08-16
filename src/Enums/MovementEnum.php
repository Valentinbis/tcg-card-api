<?php

namespace App\Enums;

enum MovementEnum: string
{
    case Expense = "expense";
    case Income = "income";

    public static function getAsArray(): array
    {
        return array_reduce(
            self::cases(),
            static fn (array $choices,  MovementEnum $type) => $choices + [$type->name => $type->value],
            [],
        );
    }
}
