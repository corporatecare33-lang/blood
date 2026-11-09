@props(['variant' => 'info', 'title' => null])
@php
    $styles = [
        'info' => ['bg-muted border-border text-ink', 'info', 'text-ink-soft'],
        'success' => ['bg-leaf-soft border-leaf/25 text-ink', 'circle-check', 'text-leaf'],
        'error' => ['bg-crimson-soft border-crimson/25 text-ink', 'circle-alert', 'text-destructive'],
    ][$variant];
@endphp
<div {{ $attributes->class("flex gap-3 rounded-lg border px-4 py-3 {$styles[0]}") }} role="{{ $variant === 'error' ? 'alert' : 'status' }}">
    <x-dynamic-component :component="'lucide-'.$styles[1]" class="mt-0.5 size-5 shrink-0 {{ $styles[2] }}" aria-hidden="true" />
    <div class="text-[0.9375rem]">
        @if ($title)<p class="font-semibold">{{ $title }}</p>@endif
        {{ $slot }}
    </div>
</div>
