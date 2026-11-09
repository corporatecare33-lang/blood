@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'submit',
    'navigate' => true,
])
@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold whitespace-nowrap transition-colors duration-150 disabled:pointer-events-none disabled:opacity-60 [&_svg]:size-[1.1em] [&_svg]:shrink-0';
    $variants = [
        'primary' => 'bg-primary text-primary-foreground hover:bg-primary-hover',
        'secondary' => 'bg-ink text-white hover:bg-ink/85',
        'outline' => 'border border-input bg-white text-ink hover:border-crimson hover:text-crimson',
        'soft' => 'border border-crimson/30 bg-white text-crimson hover:bg-crimson-soft',
        'ghost' => 'text-ink hover:bg-muted',
        'inverse' => 'bg-white text-crimson hover:bg-crimson-soft',
        'destructive' => 'bg-destructive text-white hover:bg-destructive/90',
        'link' => 'text-crimson underline-offset-4 hover:underline px-0! h-auto!',
    ];
    $sizes = [
        'xs' => 'h-8 px-2.5 text-sm',
        'sm' => 'h-9 px-3 text-sm',
        'icon' => 'size-9',
        'icon-sm' => 'size-8',
        'md' => 'h-11 px-5 text-[0.9375rem]',
        'lg' => 'h-12 px-6 text-base',
    ];
    $classes = "$base {$variants[$variant]} {$sizes[$size]}";
@endphp

@if ($href)
    <a href="{{ $href }}" @if ($navigate) wire:navigate @endif {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
