<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Organisation facts shown on the public site. Defaults are the verified values in
 * config/bandhan.php; admins can override them from /admin/settings.
 */
class Site
{
    /** Keys admins may edit, mapped to their config/bandhan.php default. */
    public const EDITABLE = [
        'email' => 'bandhan.email',
        'sponsor_name' => 'bandhan.sponsor',
        'conditions' => 'bandhan.conditions',
    ];

    /** Per-request memo lives in the container so it never leaks between requests or tests. */
    private const MEMO = 'site.settings.memo';

    public static function get(string $key): mixed
    {
        $overrides = self::overrides();

        return $overrides[$key] ?? config(self::EDITABLE[$key]);
    }

    public static function email(): string
    {
        return self::get('email');
    }

    /** Shown as plain text: the owner wants no links to other websites. */
    public static function sponsor(): string
    {
        return self::get('sponsor_name');
    }

    /** @return list<string> */
    public static function conditions(): array
    {
        return array_values(self::get('conditions'));
    }

    /** @param array<string, mixed> $values */
    public static function save(array $values): void
    {
        foreach ($values as $key => $value) {
            abort_unless(array_key_exists($key, self::EDITABLE), 422);

            // Storing the config default again would freeze it; drop the override instead.
            if ($value === config(self::EDITABLE[$key])) {
                SiteSetting::whereKey($key)->delete();
            } else {
                SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        self::forget();
    }

    public static function forget(): void
    {
        app()->forgetInstance(self::MEMO);
        Cache::forget('site.settings');
    }

    private static function overrides(): array
    {
        if (app()->bound(self::MEMO)) {
            return app(self::MEMO);
        }

        try {
            $values = Cache::rememberForever('site.settings', fn () => SiteSetting::pluck('value', 'key')->all());
        } catch (Throwable) {
            // Table not migrated yet (fresh install): fall back to config.
            $values = [];
        }

        app()->instance(self::MEMO, $values);

        return $values;
    }
}
