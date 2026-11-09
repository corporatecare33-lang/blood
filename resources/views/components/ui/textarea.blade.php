@props(['error' => null])
@php
    $key = $error ?? $attributes->wire('model')->value() ?? $attributes->get('name');
    $id = $attributes->get('id');
    $invalid = $key && $errors->has($key);
@endphp
<textarea
    @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
    {{ $attributes->merge(['rows' => 4])->class([
        'block w-full rounded-lg border bg-white px-3.5 py-3 text-base text-ink placeholder:text-ink-soft/60 transition-colors',
        'focus:border-crimson focus:outline-none focus:ring-3 focus:ring-crimson/15 focus-visible:outline-none',
        'border-destructive' => $invalid,
        'border-input' => ! $invalid,
    ]) }}
></textarea>
