@props(['variant' => 'neutral'])
@php
    $variants = [
        'neutral' => 'bg-muted text-ink-soft',
        'success' => 'bg-leaf-soft text-leaf',
        'danger' => 'bg-crimson-soft text-crimson-deep',
        'warning' => 'bg-marigold/15 text-[#7a5600]',
    ];
@endphp
<span {{ $attributes->class("inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-2xs font-semibold {$variants[$variant]}") }}>{{ $slot }}</span>
