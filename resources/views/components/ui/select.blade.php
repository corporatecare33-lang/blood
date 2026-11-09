@props(['placeholder' => null, 'error' => null, 'compact' => false])
@php
    $key = $error ?? $attributes->wire('model')->value() ?? $attributes->get('name');
    $id = $attributes->get('id');
    $invalid = $key && $errors->has($key);
@endphp
<div class="relative">
    <select
        @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class([
            'block w-full appearance-none rounded-lg border bg-white text-ink transition-colors',
            'h-9 pr-8 pl-3 text-sm shadow-xs' => $compact,
            'h-12 pr-10 pl-3.5 text-base' => ! $compact,
            'focus:border-crimson focus:outline-none focus:ring-3 focus:ring-crimson/15 focus-visible:outline-none',
            'disabled:cursor-not-allowed disabled:bg-muted disabled:text-ink-soft',
            'border-destructive' => $invalid,
            'border-input' => ! $invalid,
        ]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>
    <x-lucide-chevron-down class="pointer-events-none absolute top-1/2 {{ $compact ? 'right-2.5' : 'right-3' }} size-4 -translate-y-1/2 text-ink-soft" aria-hidden="true" />
</div>
