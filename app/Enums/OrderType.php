<?php

namespace App\Enums;

enum OrderType: string
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}
