{{-- Page intro. On mobile the app bar already names the page, so this is the page's H1 + one line of context. --}}
@props(['title', 'intro' => null])

<section class="container-page pt-6 md:pt-10">
    <x-site.breadcrumbs data-intro class="mb-4" />

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="max-w-2xl">
            <h1 data-intro class="text-[1.75rem] font-bold md:text-4xl">{{ $title }}</h1>
            @if ($intro)
                <p data-intro class="mt-1.5 text-ink-soft md:text-lg">{{ $intro }}</p>
            @endif
        </div>
        @isset($actions)
            <div data-intro class="flex shrink-0 flex-wrap gap-3">{{ $actions }}</div>
        @endisset
    </div>
</section>
