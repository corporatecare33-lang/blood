<?php

namespace App\Enums;

enum Role: string
{
    case Donor = 'donor';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::Donor => 'ডোনার',
            self::Admin => 'অ্যাডমিন',
            self::SuperAdmin => 'সুপার অ্যাডমিন',
        };
    }
}
