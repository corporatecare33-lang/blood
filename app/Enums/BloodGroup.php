<?php

namespace App\Enums;

enum BloodGroup: string
{
    case APos = 'A+';
    case ANeg = 'A-';
    case BPos = 'B+';
    case BNeg = 'B-';
    case ABPos = 'AB+';
    case ABNeg = 'AB-';
    case OPos = 'O+';
    case ONeg = 'O-';

    /** URL-safe slug, e.g. "ab-negative". */
    public function slug(): string
    {
        return strtolower(strtr($this->value, ['+' => '-positive', '-' => '-negative']));
    }

    public static function fromSlug(?string $slug): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->slug() === $slug || $case->value === $slug) {
                return $case;
            }
        }

        return null;
    }

    /** Groups whose red cells this group can receive. */
    public function canReceiveFrom(): array
    {
        return match ($this) {
            self::ONeg => [self::ONeg],
            self::OPos => [self::OPos, self::ONeg],
            self::ANeg => [self::ANeg, self::ONeg],
            self::APos => [self::APos, self::ANeg, self::OPos, self::ONeg],
            self::BNeg => [self::BNeg, self::ONeg],
            self::BPos => [self::BPos, self::BNeg, self::OPos, self::ONeg],
            self::ABNeg => [self::ABNeg, self::ANeg, self::BNeg, self::ONeg],
            self::ABPos => self::cases(),
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $g) => $g->value, self::cases());
    }
}
