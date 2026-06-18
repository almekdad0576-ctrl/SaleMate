<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}
