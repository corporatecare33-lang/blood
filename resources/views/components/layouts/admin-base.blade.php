{{-- Document shell for the admin panel and its sign-in page: no public header, footer or tab bar. --}}
@props(['bodyClass' => ''])
@php($seo = \App\Support\Seo::make())
@php(\Livewire\Livewire::forceAssetInjection())
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo->fullTitle() }}</title>
    <meta name="description" content="{{ $seo->description }}">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="/favicon.ico" sizes="48x48">
    <link rel="icon" href="/favicon-32.png" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <meta name="theme-color" content="#f6efee">

    {{-- Motion start states (see layouts/app) + the remembered sidebar width, applied before first paint and after every wire:navigate. --}}
    <script>
        (function () {
            var root = document.documentElement;
            root.classList.add('motion');
            setTimeout(function () { root.classList.add('motion-ready') }, 3000);
            function sidebar() { try { root.classList.toggle('sidebar-collapsed', localStorage.getItem('admin-sidebar-collapsed') === '1') } catch (e) {} }
            sidebar();
            document.addEventListener('livewire:navigated', sidebar);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body {{ $attributes->class(['min-h-dvh', $bodyClass]) }}>
    <a href="#content" class="sr-only z-50 rounded-md bg-ink px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">মূল অংশে যান</a>
    {{ $slot }}
    <x-site.toast placement="admin" />
</body>
</html>
