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
            Storage::disk('public')->delete([$item->image_path, $item->thumb_path]);
            SiteCache::forgetGallery();
        });
    }

    public function imageUrl(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }

    public function thumbUrl(): string
    {
        return Storage::disk('public')->url($this->thumb_path);
    }
}
