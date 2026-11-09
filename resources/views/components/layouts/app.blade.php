@props(['bare' => false])
@php($seo = \App\Support\Seo::make())
{{-- Every page needs Livewire's JS (Alpine, wire:navigate, motion hooks), not only pages with a component. --}}
@php(\Livewire\Livewire::forceAssetInjection())
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo->fullTitle() }}</title>
    <meta name="description" content="{{ $seo->description }}">
    @if ($seo->withCanonical)<link rel="canonical" href="{{ $seo->canonicalUrl() }}">@endif
    <meta name="robots" content="{{ $seo->index ? 'index, follow' : 'noindex, nofollow' }}">

    <meta property="og:type" content="{{ $seo->type }}">
    <meta property="og:site_name" content="RoktoDoot">
    <meta property="og:title" content="{{ $seo->fullTitle() }}">
    <meta property="og:description" content="{{ $seo->description }}">
    <meta property="og:url" content="{{ $seo->canonicalUrl() }}">
    <meta property="og:image" content="{{ $seo->imageUrl() }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="রক্তদূত লোগো — পানিতে ছড়িয়ে পড়া লাল রঙের পাশে">
    <meta property="og:locale" content="bn_BD">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo->fullTitle() }}">
    <meta name="twitter:description" content="{{ $seo->description }}">
    <meta name="twitter:image" content="{{ $seo->imageUrl() }}">

    <link rel="icon" href="/favicon.ico" sizes="48x48">
    <link rel="icon" href="/favicon-32.png" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#cf1f2a">

    <script type="application/ld+json">{!! json_encode($seo->jsonLd(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

    {{-- Hide animated elements until GSAP sets their start state; never longer than 3s. --}}
    <script>document.documentElement.classList.add('motion');setTimeout(function(){document.documentElement.classList.add('motion-ready')},3000)</script>

    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col pb-tabbar lg:pb-0">
    <a href="#content" class="sr-only z-50 rounded-md bg-ink px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">মূল অংশে যান</a>

    @unless ($bare)
        <x-site.header />
    @endunless

    <main id="content" class="flex-1" tabindex="-1">
        {{ $slot }}
    </main>

    @unless ($bare)
        <x-site.footer />
        <x-site.tab-bar />
    @endunless

    <x-site.toast />
</body>
</html>
