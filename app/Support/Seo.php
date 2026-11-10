<?php

namespace App\Support;

/**
 * Per-request SEO state. Controllers fill it; layouts/app.blade.php renders it.
 */
class Seo
{
    public string $title = '';

    public string $description = '';

    public ?string $canonical = null;

    /** Error pages must not declare a canonical for the bad URL. */
    public bool $withCanonical = true;

    public string $image = '/images/og/roktodoot-share.jpg';

    /** @var array{int, int} */
    public array $imageSize = [1200, 630];

    public string $imageAlt = 'রক্তদূত লোগো — পানিতে ছড়িয়ে পড়া লাল রঙের পাশে';

    /** og:type article: published / modified ISO-8601 times. */
    public ?string $publishedTime = null;

    public ?string $modifiedTime = null;

    public string $type = 'website';

    public bool $index = true;

    /** Short title shown in the mobile app bar; null on the home page (shows the brand). */
    public ?string $appBar = null;

    /** @var array<int, array{name: string, url: string}> */
    public array $breadcrumbs = [];

    /** @var array<int, array> extra JSON-LD nodes */
    public array $schema = [];

    public static function make(): self
    {
        return app(self::class);
    }

    public function title(string $title): self
    {
        $this->title = $title;
        $this->appBar ??= $title;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function canonical(string $url): self
    {
        $this->canonical = $url;

        return $this;
    }

    /** Share image for this page; $url may be a path or an absolute URL. */
    public function image(string $url, int $width, int $height, string $alt): self
    {
        $this->image = $url;
        $this->imageSize = [$width, $height];
        $this->imageAlt = $alt;

        return $this;
    }

    public function article(\DateTimeInterface $published, \DateTimeInterface $modified): self
    {
        $this->type = 'article';
        $this->publishedTime = $published->format(DATE_ATOM);
        $this->modifiedTime = $modified->format(DATE_ATOM);

        return $this;
    }

    public function appBar(string $label): self
    {
        $this->appBar = $label;

        return $this;
    }

    public function withoutCanonical(): self
    {
        $this->withCanonical = false;

        return $this;
    }

    public function noindex(): self
    {
        $this->index = false;

        return $this;
    }

    /** @param array<string, string> $trail label => url, excluding Home */
    public function breadcrumbs(array $trail): self
    {
        $this->breadcrumbs = [['name' => 'হোম', 'url' => url('/')]];
        foreach ($trail as $name => $url) {
            $this->breadcrumbs[] = ['name' => $name, 'url' => $url];
        }

        return $this;
    }

    public function schema(array $node): self
    {
        $this->schema[] = $node;

        return $this;
    }

    public function fullTitle(): string
    {
        return $this->title ? "{$this->title} | রক্তদূত" : 'রক্তদূত — স্বেচ্ছায় রক্তদাতাদের সংগঠন | RoktoDoot';
    }

    public function canonicalUrl(): string
    {
        return $this->canonical ?? url()->current();
    }

    public function imageUrl(): string
    {
        return url($this->image);
    }

    public function jsonLd(): array
    {
        $org = [
            '@type' => 'NGO',
            '@id' => url('/').'#organization',
            'name' => 'RoktoDoot',
            'alternateName' => 'রক্তদূত',
            'url' => url('/'),
            'logo' => url('/images/brand/bandhan-logo-480.png'),
            'email' => Site::email(),
            'description' => config('bandhan.description_bn'),
            'areaServed' => ['@type' => 'Country', 'name' => 'Bangladesh'],
        ];

        $graph = [
            $org,
            [
                '@type' => 'WebSite',
                '@id' => url('/').'#website',
                'url' => url('/'),
                'name' => 'RoktoDoot',
                'inLanguage' => 'bn',
                'publisher' => ['@id' => url('/').'#organization'],
            ],
            [
                '@type' => 'WebPage',
                '@id' => $this->canonicalUrl().'#webpage',
                'url' => $this->canonicalUrl(),
                'name' => $this->fullTitle(),
                'description' => $this->description,
                'inLanguage' => 'bn',
                'isPartOf' => ['@id' => url('/').'#website'],
            ],
        ];

        if (count($this->breadcrumbs) > 1) {
            $graph[] = [
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect($this->breadcrumbs)->values()->map(fn ($crumb, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $crumb['name'],
                    'item' => $crumb['url'],
                ])->all(),
            ];
        }

        return ['@context' => 'https://schema.org', '@graph' => array_merge($graph, $this->schema)];
    }
}
