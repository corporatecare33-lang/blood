<?php

namespace App\Support;

/** Bangladeshi mobile number helpers. Canonical form: 01XXXXXXXXX (11 digits). */
class Mobile
{
    public const RULE = 'regex:/^01[3-9]\d{8}$/';

    public static function normalize(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }
}
