<?php

namespace App\Models;

use App\Support\SiteCache;
use Illuminate\Database\Eloquent\Model;

class AboutSection extends Model
{
    protected $fillable = ['title', 'body', 'sort'];

    protected static function booted(): void
    {
        static::saved(fn () => SiteCache::forgetAbout());
        static::deleted(fn () => SiteCache::forgetAbout());
    }
}
