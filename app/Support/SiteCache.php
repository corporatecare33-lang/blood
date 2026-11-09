<?php

namespace App\Support;

use App\Models\AboutSection;
use App\Models\BloodRequest;
use App\Models\District;
use App\Models\Division;
use App\Models\GalleryItem;
use App\Models\Thana;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Central place for every cached read used by public pages.
 * Models flush their own keys on save/delete (see each model's booted()).
 */
class SiteCache
{
    private const DAY = 86400;

    public static function divisions(): Collection
    {
        return Cache::remember('geo.divisions', self::DAY, fn () => Division::orderBy('bn_name')->get());
    }

    public static function districts(): Collection
    {
        return Cache::remember('geo.districts', self::DAY, fn () => District::orderBy('bn_name')->get());
    }

    public static function districtsFor(?int $divisionId): Collection
    {
        return $divisionId
            ? self::districts()->where('division_id', $divisionId)->values()
            : collect();
    }

    public static function thanasFor(?int $districtId): Collection
    {
        if (! $districtId) {
            return collect();
        }

        return Cache::remember("geo.thanas.$districtId", self::DAY,
            fn () => Thana::where('district_id', $districtId)->orderBy('bn_name')->get());
    }

    public static function about(): Collection
    {
        return Cache::remember('content.about', self::DAY, fn () => AboutSection::orderBy('sort')->get());
    }

    public static function gallery(): Collection
    {
        return Cache::remember('content.gallery', self::DAY,
            fn () => GalleryItem::where('is_published', true)->orderBy('sort')->latest()->get());
    }

    public static function latestRequests(int $limit = 6): Collection
    {
        return Cache::remember("requests.latest.$limit", 120,
            fn () => BloodRequest::active()->with('district')->orderBy('needed_at')->limit($limit)->get());
    }

    public static function donorCount(): int
    {
        return Cache::remember('stats.donors', 600, fn () => User::donors()->count());
    }

    public static function forgetLocations(): void
    {
        Cache::forget('geo.divisions');
        Cache::forget('geo.districts');
        foreach (District::pluck('id') as $id) {
            Cache::forget("geo.thanas.$id");
        }
    }

    public static function forgetAbout(): void
    {
        Cache::forget('content.about');
    }

    public static function forgetGallery(): void
    {
        Cache::forget('content.gallery');
    }

    public static function forgetRequests(): void
    {
        foreach ([3, 6] as $limit) {
            Cache::forget("requests.latest.$limit");
        }
    }

    public static function forgetDonorCount(): void
    {
        Cache::forget('stats.donors');
    }
}
