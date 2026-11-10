{{--
    Blog post teaser. The title link is stretched over the whole card, so the card is one tab stop.
    layout: stack (image on top) · row (small square thumbnail beside the title, for sidebars)
            · row-mobile (row below `sm`, stack from `sm` up — used on the home page).
--}}
@props(['post', 'level' => 'h3', 'layout' => 'stack', 'eager' => false])
@php
    [$tw, $th] = $post->hasCover() ? $post->thumbSize() : [0, 0];
    $stack = $layout === 'stack';
    $row = $layout === 'row';
    $mobileRow = $layout === 'row-mobile';
@endphp

<article {{ $attributes->class([
    'group relative flex overflow-hidden rounded-xl bg-card shadow-card transition-shadow duration-200 hover:shadow-panel',
    'has-[a:focus-visible]:outline-3 has-[a:focus-visible]:outline-offset-2 has-[a:focus-visible]:outline-marigold',
    'flex-col' => $stack,
    'items-center gap-3.5 p-3' => $row || $mobileRow,
    'sm:flex-col sm:items-stretch sm:gap-0 sm:p-0' => $mobileRow,
]) }}>
    <div @class([
        'relative shrink-0 overflow-hidden bg-crimson-soft',
        'aspect-[16/10] w-full' => $stack,
        'size-20 rounded-lg' => $row,
        'size-24 rounded-lg sm:aspect-[16/10] sm:size-auto sm:w-full sm:rounded-none' => $mobileRow,
    ])>
        @if ($post->hasCover())
            <img src="{{ $post->coverThumbUrl() }}" alt="" width="{{ $tw }}" height="{{ $th }}"
                 loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async"
                 class="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]">
        @else
            <span class="flex size-full items-center justify-center" aria-hidden="true">
                <x-lucide-droplet @class(['fill-crimson/15 text-crimson/40', 'size-10' => $stack, 'size-7' => $row, 'size-8 sm:size-10' => $mobileRow]) />
            </span>
        @endif
    </div>

    <div @class(['flex min-w-0 flex-1 flex-col', 'p-4 sm:p-5' => $stack, 'sm:p-5' => $mobileRow])>
        <p class="text-sm text-ink-soft">
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ \App\Support\Bn::date($post->published_at) }}</time>
            @unless ($row)
                <span @class(['hidden sm:inline' => $mobileRow])>· {{ \App\Support\Bn::num($post->readingMinutes()) }} মিনিটে পড়া</span>
            @endunless
        </p>
        <{{ $level }} @class([
            'mt-1 font-sans font-bold leading-snug text-ink',
            'text-lg' => $stack,
            'line-clamp-3 text-base' => $row || $mobileRow,
            'sm:line-clamp-none sm:text-lg' => $mobileRow,
        ])>
            <a href="{{ $post->url() }}" wire:navigate class="transition-colors after:absolute after:inset-0 focus-visible:outline-none group-hover:text-crimson">{{ $post->title }}</a>
        </{{ $level }}>
        @unless ($row)
            <p @class(['mt-1.5 line-clamp-3 text-[0.9375rem] text-ink-soft', 'hidden sm:block' => $mobileRow])>{{ $post->summary() }}</p>
            <span @class(['mt-auto items-center gap-1 pt-4 text-[0.9375rem] font-semibold text-crimson', 'inline-flex' => $stack, 'hidden sm:inline-flex' => $mobileRow]) aria-hidden="true">
                পড়ুন<x-lucide-arrow-right class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
            </span>
        @endunless
    </div>
</article>
