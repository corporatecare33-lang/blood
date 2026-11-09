<?php

namespace App\Enums;

enum RequestStatus: string
{
    case Open = 'open';
    case Fulfilled = 'fulfilled';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'ডোনার প্রয়োজন',
            self::Fulfilled => 'ডোনার পাওয়া গেছে',
            self::Closed => 'বন্ধ',
        };
    }
}
