<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}
