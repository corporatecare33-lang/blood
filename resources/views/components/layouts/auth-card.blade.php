{{-- Narrow centred card used by log in and password reset. --}}
@props(['title', 'intro' => null])

<x-layouts.app>
    <div class="container-page max-w-md! pt-6 pb-8 md:pt-14">
        <div data-intro class="card p-6 sm:p-8">
            <span class="flex size-12 items-center justify-center rounded-full bg-crimson text-white" aria-hidden="true"><x-lucide-droplet class="size-6 fill-white" /></span>
            <h1 class="mt-4 text-[1.75rem] font-bold">{{ $title }}</h1>
            @if ($intro)<p class="mt-1 text-ink-soft">{{ $intro }}</p>@endif
            <div class="mt-6">{{ $slot }}</div>
        </div>
        @isset($footer)
            <div data-intro class="mt-5 text-center text-[0.9375rem] text-ink-soft">{{ $footer }}</div>
        @endisset
    </div>
</x-layouts.app>
