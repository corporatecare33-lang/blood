<?php

namespace App\Models;

use App\Support\Site;
use Illuminate\Database\Eloquent\Model;

/** Admin-edited overrides for the facts in config/bandhan.php (see App\Support\Site). */
class SiteSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Site::forget());
        static::deleted(fn () => Site::forget());
    }
}
