{{-- level: heading rank that fits the outline where it's placed (2 when directly under the page h1). --}}
@props(['icon' => 'droplet', 'title', 'level' => 3])

<div {{ $attributes->class('card flex flex-col items-center px-6 py-10 text-center') }}>
    <span class="flex size-12 items-center justify-center rounded-full bg-crimson-soft text-crimson">
        <x-dynamic-component :component="'lucide-'.$icon" class="size-6" aria-hidden="true" />
    </span>
    <h{{ (int) $level }} class="mt-4 font-sans text-lg font-bold">{{ $title }}</h{{ (int) $level }}>
    @if (trim($slot))
        <div class="mt-1.5 max-w-md text-[0.9375rem] text-ink-soft">{{ $slot }}</div>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
