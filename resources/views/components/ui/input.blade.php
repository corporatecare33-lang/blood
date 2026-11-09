@props(['name' => null, 'error' => null, 'compact' => false])
@php
    $key = $error ?? $name ?? $attributes->wire('model')->value();
    $id = $attributes->get('id');
    $invalid = $key && $errors->has($key);
@endphp
<input
    @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
    {{ $attributes->class([
        'block w-full rounded-lg border bg-white text-ink placeholder:text-ink-soft/60 transition-colors',
        'focus:border-crimson focus:outline-none focus:ring-3 focus:ring-crimson/15 focus-visible:outline-none',
        'disabled:cursor-not-allowed disabled:bg-muted',
        'h-9 px-3 text-sm shadow-xs' => $compact,
        'h-12 px-3.5 text-base' => ! $compact,
        'border-destructive' => $invalid,
        'border-input' => ! $invalid,
    ]) }}
    @if ($name) name="{{ $name }}" @endif
>
