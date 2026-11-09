<x-layouts.app>
    {{-- Hero: the organisation's own motto is the headline; the heartbeat trace echoes the line inside the logo's heart. --}}
    <section data-hero class="container-page pt-4 md:pt-8">
        <x-site.breadcrumbs class="mb-4" />

        <div class="relative isolate overflow-hidden rounded-xl bg-crimson text-white shadow-panel">
            <svg data-draw class="pointer-events-none absolute inset-x-0 bottom-4 -z-10 h-24 w-full text-white/15 lg:bottom-8 lg:h-32" viewBox="0 0 1200 140" preserveAspectRatio="none" fill="none" aria-hidden="true">
                <path d="M0 96 H640 l18 -10 l16 10 h22 l14 -78 l18 120 l14 -66 l12 24 h34 l20 -16 l20 16 H1200" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
            </svg>

            <div class="grid gap-8 p-6 sm:p-8 md:p-12 lg:grid-cols-12 lg:items-end lg:gap-12">
                <div class="lg:col-span-7">
                    <p data-hero-line class="text-[0.9375rem] font-medium text-white/85">রক্তদূত, {{ config('bandhan.subtitle_bn') }}</p>
                    <h1 data-hero-line class="mt-3 text-[clamp(1.6rem,8vw,3.5rem)] lg:text-[3.25rem] xl:text-[3.5rem] leading-[1.15] font-extrabold text-white">
                        <span class="block whitespace-nowrap">রক্ত দিন জীবন বাঁচান।</span>
                        <span class="block whitespace-nowrap">রক্ত নিন নিজে বাঁচুন।</span>
                    </h1>
                    <p data-hero-line class="mt-5 max-w-xl text-[1.0625rem] text-white/90 md:text-lg">{{ config('bandhan.description_bn') }}</p>
                    <p data-hero-line class="mt-6 flex items-center gap-3 text-[0.9375rem]">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full border border-white/60" aria-hidden="true">
                            <x-lucide-droplet class="size-5 fill-white/90" />
                        </span>
                        @if ($donorCount > 0)
                            <span><strong data-count-to="{{ $donorCount }}" class="font-display text-xl tabular-nums">{{ \App\Support\Bn::num($donorCount) }}</strong> জন রক্তযোদ্ধা এখন রক্তদূতে নিবন্ধিত</span>
                        @else
                            <span>প্রথম রক্তযোদ্ধাদের একজন হোন</span>
                        @endif
                    </p>
                </div>

                @php($whyIcons = ['bot', 'hand-coins', 'clock-4', 'shield-check'])
                <ul data-hero-panel class="grid grid-cols-2 gap-2.5 lg:col-span-5" aria-label="কেন রক্তদূত">
                    @foreach (config('bandhan.why') as $i => $point)
                        <li class="flex flex-col gap-3 rounded-lg border border-white/15 bg-white/10 p-4 backdrop-blur-[2px] sm:p-5">
                            <span class="flex size-10 items-center justify-center rounded-full bg-white text-crimson" aria-hidden="true">
                                <x-dynamic-component :component="'lucide-'.($whyIcons[$i] ?? 'check')" class="size-5" />
                            </span>
                            <span class="font-display text-lg leading-snug font-semibold sm:text-xl">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <div class="container-page section grid gap-8 lg:grid-cols-12 lg:items-start lg:gap-10">
        <div class="lg:col-span-8">
            {{-- Admin-editable sections (/admin/about), read as one document rather than separate cards. --}}
            @if ($sections->isNotEmpty())
                <div data-reveal class="card divide-y divide-border">
                    @foreach ($sections as $section)
                        <section aria-labelledby="about-{{ $section->id }}" class="grid gap-2 p-5 sm:p-7 md:grid-cols-[11rem_1fr] md:gap-8">
                            <h2 id="about-{{ $section->id }}" class="flex items-start gap-2.5 text-xl font-bold md:text-[1.375rem] md:leading-snug">
                                <x-lucide-droplet class="mt-0.5 size-5 shrink-0 fill-crimson text-crimson md:mt-1" aria-hidden="true" />
                                {{ $section->title }}
                            </h2>
                            <div class="prose-bandhan">
                                @foreach (preg_split('/\n\s*\n/', trim($section->body)) as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif

            <x-site.conditions @class(['mt-12' => $sections->isNotEmpty()]) />
        </div>

        <aside class="lg:sticky lg:top-24 lg:col-span-4" aria-label="যুক্ত হন">
            <div data-reveal class="card overflow-hidden">
                <div class="p-5 sm:p-6">
                    <span class="flex size-12 items-center justify-center rounded-full bg-crimson-soft text-crimson" aria-hidden="true">
                        <x-lucide-hand-heart class="size-6" />
                    </span>
                    <h2 class="mt-4 text-2xl font-bold">সাহায্য করতে চান?</h2>
                    <p class="mt-1.5 text-ink-soft">ডোনার হিসেবে যুক্ত হন, অথবা পরিচিত কারো রক্ত দরকার হলে অনুরোধ পোস্ট করুন।</p>
                    <div class="mt-5 grid gap-2.5">
                        <x-ui.button :href="route('register')" size="lg"><x-lucide-user-plus aria-hidden="true" />ডোনার হিসেবে যুক্ত হন</x-ui.button>
                        <x-ui.button :href="route('requests.create')" variant="soft" size="lg"><x-lucide-send aria-hidden="true" />অনুরোধ পোস্ট করুন</x-ui.button>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.app>
