{{--
    Admins only: stands in for a home-page section that is hidden while it has no content,
    so an empty blog or video gallery doesn't look like a missing feature. Visitors never see it.
--}}
@props(['heading', 'id', 'icon', 'action', 'href', 'actionIcon' => 'plus'])

<section {{ $attributes->class('container-page mt-12') }} aria-labelledby="{{ $id }}">
    <h2 id="{{ $id }}" class="text-2xl font-bold md:text-3xl">{{ $heading }}</h2>
    <div class="mt-4 flex flex-col gap-4 rounded-xl border-2 border-dashed border-crimson/30 bg-white/60 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div class="flex gap-4">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-crimson-soft text-crimson" aria-hidden="true">
                <x-dynamic-component :component="'lucide-'.$icon" class="size-6" />
            </span>
            <div>
                <p class="font-semibold">এই অংশটি শুধু আপনি (অ্যাডমিন) দেখছেন</p>
                <p class="mt-0.5 text-[0.9375rem] text-ink-soft">{{ $slot }}</p>
            </div>
        </div>
        <x-ui.button :href="$href" class="shrink-0"><x-dynamic-component :component="'lucide-'.$actionIcon" aria-hidden="true" />{{ $action }}</x-ui.button>
    </div>
</section>
