<?php

namespace App\Support;

use Carbon\CarbonInterface;

/** Bangla display helpers: digits, dates and relative times. */
class Bn
{
    private const DIGITS = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];

    /** Converts Latin digits to Bangla digits; numbers get thousands separators. */
    public static function num(int|float|string|null $value): string
    {
        if (is_int($value) || is_float($value)) {
            $value = number_format($value);
        }

        return strtr((string) $value, self::DIGITS);
    }

    /** e.g. "৯ নভেম্বর ২০২৬" */
    public static function date(?CarbonInterface $date): string
    {
        return $date ? self::num($date->copy()->locale('bn')->translatedFormat('j F Y')) : '';
    }

    /** e.g. "সোমবার, ৯ নভেম্বর, বিকাল ৩:৩০" */
    public static function dateTime(?CarbonInterface $date): string
    {
        if (! $date) {
            return '';
        }

        $local = $date->copy()->locale('bn');

        return self::num($local->translatedFormat('l, j F')).', '.self::time($date);
    }

    /** e.g. "বিকাল ৩:৩০" */
    public static function time(?CarbonInterface $date): string
    {
        return $date ? self::num(self::dayPart($date).' '.$date->format('g:i')) : '';
    }

    /** e.g. "২ ঘন্টা আগে" */
    public static function ago(?CarbonInterface $date): string
    {
        return $date ? self::num($date->copy()->locale('bn')->diffForHumans()) : '';
    }

    private static function dayPart(CarbonInterface $date): string
    {
        return match (true) {
            $date->hour < 4 => 'রাত',
            $date->hour < 6 => 'ভোর',
            $date->hour < 12 => 'সকাল',
            $date->hour < 15 => 'দুপুর',
            $date->hour < 18 => 'বিকাল',
            $date->hour < 20 => 'সন্ধ্যা',
            default => 'রাত',
        };
    }
}
