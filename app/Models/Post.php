<?php

namespace App\Models;

use App\Support\SiteCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

/**
 * Blog post (/blog). Body is Markdown written by admins; raw HTML in it is escaped.
 * A post is public when is_published and published_at has passed (future dates = scheduled).
 */
class Post extends Model
{
    public const THUMB_WIDTH = 720;

    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'body',
        'cover_path', 'cover_thumb_path', 'cover_width', 'cover_height', 'cover_alt',
        'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => SiteCache::forgetPosts());
        static::deleted(function (self $post) {
            $post->deleteCover();
            SiteCache::forgetPosts();
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->where('published_at', '<=', now());
    }

    /** Newest first, as on /blog. */
    public function scopeLatestPublished(Builder $query): void
    {
        $query->published()->orderByDesc('published_at')->orderByDesc('id');
    }

    public function isLive(): bool
    {
        return $this->is_published && $this->published_at && ! $this->published_at->isFuture();
    }

    public function isScheduled(): bool
    {
        return $this->is_published && $this->published_at?->isFuture();
    }

    // ---- Slugs --------------------------------------------------------------

    /** Keeps Bangla letters and vowel signs (Str::slug would transliterate them into gibberish). */
    public static function slugFrom(string $text): string
    {
        $slug = trim(preg_replace('/[^\p{L}\p{M}\p{N}]+/u', '-', mb_strtolower($text)), '-');

        if (mb_strlen($slug) > 80) {
            $cut = mb_substr($slug, 0, 80);
            $slug = rtrim(mb_strrpos($cut, '-') > 40 ? mb_substr($cut, 0, mb_strrpos($cut, '-')) : $cut, '-');
        }

        return $slug;
    }

    public static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = $base !== '' ? $base : 'post';
        $slug = $base;
        $n = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    // ---- Presentation -------------------------------------------------------

    public function url(): string
    {
        return route('blog.show', $this->slug);
    }

    public function bodyHtml(): string
    {
        $html = (string) Str::markdown($this->body, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
            'external_link' => [
                'internal_hosts' => [parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost'],
                'open_in_new_window' => true,
                'nofollow' => 'external',
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
        ], [new ExternalLinkExtension]);

        // The post title is the page's only h1.
        return preg_replace('/<(\/?)h1\b/', '<$1h2', $html);
    }

    /** Admin-written summary, or the opening of the body as plain text. */
    public function summary(int $limit = 180): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        $text = trim(preg_replace('/\s+/u', ' ', strip_tags(Str::markdown($this->body, ['html_input' => 'strip']))));

        return Str::limit($text, $limit, '…', preserveWords: true);
    }

    /** Reading time at ~180 words a minute (Bangla reads slower than English). */
    public function readingMinutes(): int
    {
        $words = count(preg_split('/\s+/u', trim(strip_tags($this->body)), -1, PREG_SPLIT_NO_EMPTY));

        return max(1, (int) ceil($words / 180));
    }

    public function hasCover(): bool
    {
        return (bool) $this->cover_path;
    }

    public function coverUrl(): ?string
    {
        return self::fileUrl($this->cover_path);
    }

    public function coverThumbUrl(): ?string
    {
        return self::fileUrl($this->cover_thumb_path);
    }

    /**
     * Uploads live on the public disk; covers shipped with the code (articles added by a migration)
     * live under public/images/ so they survive redeploys.
     */
    private static function fileUrl(?string $path): ?string
    {
        return match (true) {
            ! $path => null,
            str_starts_with($path, 'images/') => url($path),
            default => Storage::disk('public')->url($path),
        };
    }

    /** @return array{int, int} width and height of the thumbnail file */
    public function thumbSize(): array
    {
        $w = min(self::THUMB_WIDTH, (int) $this->cover_width);

        return [$w, (int) round($this->cover_height * $w / max(1, $this->cover_width))];
    }

    /** srcset covering both stored sizes; collapses to one entry for small originals. */
    public function coverSrcset(): string
    {
        [$thumbW] = $this->thumbSize();

        return $thumbW < $this->cover_width
            ? $this->coverThumbUrl()." {$thumbW}w, ".$this->coverUrl()." {$this->cover_width}w"
            : $this->coverUrl()." {$this->cover_width}w";
    }

    public function coverAlt(): string
    {
        return $this->cover_alt ?: $this->title;
    }

    public function deleteCover(): void
    {
        // Never delete files that ship with the code.
        $paths = array_filter([$this->cover_path, $this->cover_thumb_path], fn ($p) => $p && ! str_starts_with($p, 'images/'));
        if ($paths) {
            Storage::disk('public')->delete($paths);
        }
    }
}
