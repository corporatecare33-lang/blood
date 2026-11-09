<?php

namespace App\Enums;

enum Occupation: string
{
    case Student = 'Student';
    case Others = 'Others';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'শিক্ষার্থী',
            self::Others => 'অন্যান্য',
        };
    }
}
