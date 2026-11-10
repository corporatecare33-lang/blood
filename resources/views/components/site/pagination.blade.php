{{-- Link-based pagination for controller-rendered lists (crawlable, works without JS). --}}
@props(['paginator'])
@php
    $bn = fn ($n) => \App\Support\Bn::num($n);
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $window = collect(range(max(1, $current - 2), min($last, $current + 2)));
    $item = 'inline-flex h-11 min-w-11 items-center justify-center gap-1.5 rounded-lg px-3 text-[0.9375rem] font-semibold transition-colors';
@endphp

@if ($paginator->hasPages())
    <nav aria-label="পাতা বদলান" {{ $attributes->class('flex items-center justify-between gap-3 sm:justify-center') }}>
        @if ($paginator->onFirstPage())
            <span class="{{ $item }} cursor-not-allowed text-ink-soft/50" aria-disabled="true"><x-lucide-chevron-left class="size-4" aria-hidden="true" />আগের</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" wire:navigate rel="prev" class="{{ $item }} bg-white text-ink shadow-xs hover:text-crimson"><x-lucide-chevron-left class="size-4" aria-hidden="true" />আগের</a>
        @endif

        <p class="text-[0.9375rem] text-ink-soft sm:hidden">পাতা {{ $bn($current) }} / {{ $bn($last) }}</p>

        <ul class="hidden items-center gap-1 sm:flex">
            @foreach ($window as $page)
                <li>
                    @if ($page === $current)
                        <span aria-current="page" class="{{ $item }} bg-crimson text-white">{{ $bn($page) }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}" wire:navigate class="{{ $item }} text-ink hover:bg-white hover:text-crimson" aria-label="পাতা {{ $bn($page) }}">{{ $bn($page) }}</a>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" wire:navigate rel="next" class="{{ $item }} bg-white text-ink shadow-xs hover:text-crimson">পরের<x-lucide-chevron-right class="size-4" aria-hidden="true" /></a>
        @else
            <span class="{{ $item }} cursor-not-allowed text-ink-soft/50" aria-disabled="true">পরের<x-lucide-chevron-right class="size-4" aria-hidden="true" /></span>
        @endif
    </nav>
@endif
