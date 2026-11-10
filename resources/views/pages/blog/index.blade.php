@php
    $featured = $posts->onFirstPage() ? $posts->first() : null;
    $rest = $featured ? $posts->slice(1) : $posts;
@endphp

@if ($featured?->hasCover())
    @push('head')
        <link rel="preload" as="image" type="image/webp" imagesrcset="{{ $featured->coverSrcset() }}" imagesizes="(min-width: 768px) 58vw, 100vw">
    @endpush
@endif

<x-layouts.app>
    <x-site.page-header title="ব্লগ" intro="রক্তদূতের খবর, রক্তদান কর্মসূচির প্রতিবেদন আর রক্তদান নিয়ে সচেতনতামূলক লেখা।" />

    <div class="container-page section pt-5!">
        @if ($posts->isEmpty())
            <x-site.empty-state :level="2" data-reveal icon="newspaper" title="এখনো কোনো লেখা প্রকাশ হয়নি">
                রক্তদান কর্মসূচি বা সংগঠনের খবর প্রকাশ হলে এখানে দেখা যাবে। ততক্ষণ রক্তদানের আগে-পরে কী করবেন জেনে নিন।
                <x-slot:action>
                    <x-ui.button :href="route('tips')" variant="soft"><x-lucide-heart-pulse aria-hidden="true" />ডোনার টিপস পড়ুন</x-ui.button>
                </x-slot:action>
            </x-site.empty-state>
        @else
            @if ($featured)
                @php([$tw, $th] = $featured->hasCover() ? $featured->thumbSize() : [0, 0])
                <article data-intro class="group relative grid overflow-hidden rounded-xl bg-card shadow-card transition-shadow duration-200 hover:shadow-panel has-[a:focus-visible]:outline-3 has-[a:focus-visible]:outline-offset-2 has-[a:focus-visible]:outline-marigold md:grid-cols-12">
                    <div class="relative aspect-[16/10] overflow-hidden bg-crimson-soft md:col-span-7 md:aspect-auto md:min-h-[22rem]">
                        @if ($featured->hasCover())
                            <img src="{{ $featured->coverUrl() }}" srcset="{{ $featured->coverSrcset() }}" sizes="(min-width: 768px) 58vw, 100vw"
                                 alt="{{ $featured->coverAlt() }}" width="{{ $featured->cover_width }}" height="{{ $featured->cover_height }}"
                                 fetchpriority="high" decoding="async"
                                 class="absolute inset-0 size-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.02]">
                        @else
                            <span class="absolute inset-0 flex items-center justify-center" aria-hidden="true">
                                <x-lucide-droplet class="size-16 fill-crimson/15 text-crimson/40" />
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-col p-5 sm:p-7 md:col-span-5 md:justify-center lg:p-10">
                        <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-soft">
                            <span class="rounded-full bg-crimson-soft px-2.5 py-0.5 text-xs font-semibold text-crimson-deep">সর্বশেষ</span>
                            <time datetime="{{ $featured->published_at->toDateString() }}">{{ \App\Support\Bn::date($featured->published_at) }}</time>
                            <span aria-hidden="true">·</span>
                            <span>{{ \App\Support\Bn::num($featured->readingMinutes()) }} মিনিটে পড়া</span>
                        </p>
                        <h2 class="mt-3 text-[1.375rem] leading-tight font-bold sm:text-2xl md:text-[1.75rem] lg:text-3xl">
                            <a href="{{ $featured->url() }}" wire:navigate class="transition-colors after:absolute after:inset-0 focus-visible:outline-none group-hover:text-crimson">{{ $featured->title }}</a>
                        </h2>
                        <p class="mt-3 line-clamp-4 text-ink-soft md:text-[1.0625rem]">{{ $featured->summary(220) }}</p>
                        <span class="mt-5 inline-flex items-center gap-1.5 font-semibold text-crimson" aria-hidden="true">
                            পুরো লেখা পড়ুন<x-lucide-arrow-right class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                        </span>
                    </div>
                </article>
            @endif

            @if ($rest->isNotEmpty())
                @if ($featured)
                    <h2 data-reveal class="mt-12 text-2xl font-bold md:text-3xl">আরও লেখা</h2>
                @endif
                <div data-reveal-stagger @class(['grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-5', 'mt-4' => $featured])>
                    @foreach ($rest as $post)
                        <x-site.post-card :post="$post" :level="$featured ? 'h3' : 'h2'" :eager="! $featured && $loop->index < 3" />
                    @endforeach
                </div>
            @endif

            <x-site.pagination :paginator="$posts" class="mt-10" />
        @endif
    </div>
</x-layouts.app>
