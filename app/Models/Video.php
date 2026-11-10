<?php

namespace App\Models;

use App\Support\SiteCache;
use Illuminate\Database\Eloquent\Model;

/** A YouTube video shown in the home page's "ভিডিও গ্যালারি". Admins paste any YouTube link; only the id is stored. */
class Video extends Model
{
    protected $fillable = ['title', 'youtube_id', 'sort', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => SiteCache::forgetVideos());
        static::deleted(fn () => SiteCache::forgetVideos());
    }

    /**
     * The 11-character video id from a YouTube link (watch, youtu.be, shorts, embed, live, m.)
     * or a bare id. Null for anything else.
     */
    public static function idFrom(string $input): ?string
    {
        $input = trim($input);

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input)) {
            return $input;
        }

        $pattern = '~^(?:https?://)?(?:(?:www\.|m\.|music\.)?youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})(?:[?&#/].*)?$~i';

        return preg_match($pattern, $input, $m) ? $m[1] : null;
    }

    public function watchUrl(): string
    {
        return 'https://www.youtube.com/watch?v='.$this->youtube_id;
    }

    /** 480×360 (4:3 with letterbox bars — crop to 16:9 with object-cover). Exists for every video. */
    public function thumbUrl(): string
    {
        return "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg";
    }

    /** Privacy-enhanced player; only loaded after the visitor presses play. */
    public function embedUrl(): string
    {
        return "https://www.youtube-nocookie.com/embed/{$this->youtube_id}?autoplay=1&rel=0&playsinline=1";
    }
}
