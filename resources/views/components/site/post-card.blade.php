{{--
    Blog post teaser. The title link is stretched over the whole card, so the card is one tab stop.
    layout: stack (image on top) · row (small thumbnail beside the title, for sidebars).
    Covers carry their own text, so thumbnails keep the cover's 16:10 shape instead of cropping it.
--}}
@props(['post', 'level' => 'h3', 'layout' => 'stack', 'eager' => false])
@php
    [$tw, $th] = $post->hasCover() ? $post->thumbSize() : [0, 0];
    $stack = $layout === 'stack';
    $row = $layout === 'row';
@endphp

<article {{ $attributes->class([
    'group relative flex overflow-hidden rounded-xl bg-card shadow-card transition-shadow duration-200 hover:shadow-panel',
    'has-[a:focus-visible]:outline-3 has-[a:focus-visible]:outline-offset-2 has-[a:focus-visible]:outline-marigold',
    'flex-col' => $stack,
    'items-center gap-3 p-3' => $row,
]) }}>
    <div @class([
        'relative shrink-0 overflow-hidden bg-crimson-soft',
        'aspect-[16/10] w-full' => $stack,
        'aspect-[16/10] w-28 rounded-lg' => $row,
    ])>
        @if ($post->hasCover())
            <img src="{{ $post->coverThumbUrl() }}" alt="" width="{{ $tw }}" height="{{ $th }}"
                 loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async"
                 class="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]">
        @else
            <span class="flex size-full items-center justify-center" aria-hidden="true">
                <x-lucide-droplet @class(['fill-crimson/15 text-crimson/40', 'size-10' => $stack, 'size-7' => $row]) />
            </span>
        @endif
    </div>

    <div @class(['flex min-w-0 flex-1 flex-col', 'p-4 sm:p-5' => $stack])>
        <p @class(['text-ink-soft', 'text-sm' => $stack, 'text-[0.8125rem]' => $row])>
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ \App\Support\Bn::date($post->published_at) }}</time>
            @if ($stack)
                · {{ \App\Support\Bn::num($post->readingMinutes()) }} মিনিটে পড়া
            @endif
        </p>
        <{{ $level }} @class([
            'mt-1 font-sans font-bold leading-snug text-ink',
            'text-[1.0625rem] sm:text-lg' => $stack,
            'line-clamp-3 text-[0.9375rem]' => $row,
        ])>
            <a href="{{ $post->url() }}" wire:navigate class="transition-colors after:absolute after:inset-0 focus-visible:outline-none group-hover:text-crimson">{{ $post->title }}</a>
        </{{ $level }}>
        @if ($stack)
            <p class="mt-1.5 line-clamp-3 text-[0.9375rem] text-ink-soft">{{ $post->summary() }}</p>
            <span class="mt-auto inline-flex items-center gap-1 pt-4 text-[0.9375rem] font-semibold text-crimson" aria-hidden="true">
                পড়ুন<x-lucide-arrow-right class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
            </span>
        @endif
    </div>
</article>
