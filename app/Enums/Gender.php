<?php

namespace App\Enums;

enum Gender: string
{
    case Male = 'Male';
    case Female = 'Female';
    case Others = 'Others';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'পুরুষ',
            self::Female => 'মহিলা',
            self::Others => 'অন্যান্য',
        };
    }
}
