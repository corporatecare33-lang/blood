{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($pages as $route => [$freq, $priority])
    <url>
        <loc>{{ route($route) }}</loc>
@if ($route === 'gallery' && $galleryUpdated)
        <lastmod>{{ \Illuminate\Support\Carbon::parse($galleryUpdated)->toAtomString() }}</lastmod>
@endif
        <changefreq>{{ $freq }}</changefreq>
        <priority>{{ $priority }}</priority>
    </url>
@endforeach
</urlset>
