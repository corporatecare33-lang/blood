<?php

namespace App\Models;

use App\Support\SiteCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Thana extends Model
{
    protected $fillable = ['district_id', 'name', 'bn_name'];

    protected static function booted(): void
    {
        static::saved(fn () => SiteCache::forgetLocations());
        static::deleted(fn () => SiteCache::forgetLocations());
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
