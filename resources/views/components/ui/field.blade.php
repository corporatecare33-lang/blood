{{--
    Label + control + hint + error, wired for accessibility.
    Usage: <x-ui.field label="Mobile" bn="মোবাইল" for="mobile" :required="true" error="mobile"> <x-ui.input id="mobile" … /> </x-ui.field>
--}}
@props(['label', 'bn' => null, 'for', 'required' => false, 'hint' => null, 'error' => null])
@php($errorKey = $error ?? $for)

<div {{ $attributes->class('grid content-start gap-1.5') }}>
    <label for="{{ $for }}" class="text-[0.9375rem] font-medium text-ink">
        {{ $label }}@if ($bn) <span lang="bn" class="font-normal text-muted-foreground">({{ $bn }})</span>@endif
        @if ($required)<span class="text-crimson" aria-hidden="true">*</span><span class="sr-only">(আবশ্যক)</span>@endif
    </label>

    {{ $slot }}

    @if ($hint)
        <p id="{{ $for }}-hint" class="text-sm text-muted-foreground">{{ $hint }}</p>
    @endif

    @error($errorKey)
        <p id="{{ $for }}-error" class="flex items-start gap-1.5 text-sm font-medium text-destructive">
            <x-lucide-circle-alert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />{{ $message }}
        </p>
    @enderror
</div>
