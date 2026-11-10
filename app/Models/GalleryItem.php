<?php

namespace App\Models;

use App\Support\SiteCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    protected $fillable = ['title', 'caption', 'image_path', 'thumb_path', 'width', 'height', 'sort', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => SiteCache::forgetGallery());
        static::deleted(function (self $item) {
            // Never delete files that ship with the code.
            $paths = array_filter([$item->image_path, $item->thumb_path], fn ($p) => ! str_starts_with($p, 'images/'));
            if ($paths) {
                Storage::disk('public')->delete($paths);
            }
            SiteCache::forgetGallery();
        });
    }

    public function imageUrl(): string
    {
        return self::fileUrl($this->image_path);
    }

    public function thumbUrl(): string
    {
        return self::fileUrl($this->thumb_path);
    }

    /**
     * Uploads live on the public disk; photos shipped with the code (added by a migration)
     * live under public/images/ so they survive redeploys.
     */
    private static function fileUrl(string $path): string
    {
        return str_starts_with($path, 'images/') ? url($path) : Storage::disk('public')->url($path);
    }
}
