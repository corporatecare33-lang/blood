@push('head')
    <link rel="preload" as="image" type="image/webp" media="(min-width: 768px)"
          imagesrcset="/images/hero/blood-in-water-768.webp 768w, /images/hero/blood-in-water-1280.webp 1280w"
          imagesizes="40vw">
@endpush

{{-- Layout follows docs/reference/mobile-app-mockup.jpg (screen 1). --}}
<x-layouts.app>
    {{-- Hero card: "রক্ত দিয়ে জীবন বাঁচাই" --}}
    <section data-hero class="container-page pt-4 md:pt-8">
        <div class="relative overflow-hidden rounded-xl bg-crimson text-white shadow-panel md:grid md:grid-cols-12">
            <div class="relative z-10 p-6 sm:p-8 md:col-span-7 md:p-12">
                <p data-hero-line class="text-[0.9375rem] font-medium text-white/85">{{ config('bandhan.subtitle_bn') }}</p>
                <h1 data-hero-line class="mt-2 text-[2.6rem] leading-[1.05] font-extrabold text-white sm:text-5xl md:text-6xl">রক্ত দিয়ে<br>জীবন বাঁচাই</h1>
                <p data-hero-line class="mt-4 max-w-md text-[1.0625rem] text-white/90">
                    রক্তের গ্রুপ আর জেলা দিয়ে কাছের স্বেচ্ছাসেবী রক্তদাতা খুঁজুন। সম্পূর্ণ বিনামূল্যে, ২৪ ঘণ্টা।
                </p>
                <div data-hero-line class="mt-5 flex items-center gap-3 border-t border-white/20 pt-4 text-[0.9375rem]">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full border border-white/60" aria-hidden="true">
                        <x-lucide-droplet class="size-5 fill-white/90" />
                    </span>
                    <p>
                        @if ($donorCount > 0)
                            <strong class="tabular-nums">{{ \App\Support\Bn::num($donorCount) }}</strong> জন রক্তযোদ্ধা আপনার পাশে • দ্রুত ডোনার খুঁজুন
                        @else
                            প্রথম রক্তযোদ্ধাদের একজন হোন • দ্রুত ডোনার খুঁজুন
                        @endif
                    </p>
                </div>
                <div data-hero-line class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button :href="route('donors.search')" variant="inverse" size="lg"><x-lucide-search aria-hidden="true" />ডোনার খুঁজুন</x-ui.button>
                    <x-ui.button :href="route('register')" size="lg" class="border border-white/70 bg-transparent! hover:bg-white/10!"><x-lucide-hand-heart aria-hidden="true" />ডোনার হোন</x-ui.button>
                </div>
            </div>
            <div data-hero-media class="relative hidden overflow-hidden md:col-span-5 md:block">
                <picture>
                    <source type="image/webp" srcset="/images/hero/blood-in-water-768.webp 768w, /images/hero/blood-in-water-1280.webp 1280w" sizes="40vw">
                    <img src="/images/hero/blood-in-water-1280.jpg" alt="" width="1280" height="853" fetchpriority="high" decoding="async" class="absolute inset-0 size-full object-cover object-[60%_70%]">
                </picture>
                <div class="absolute inset-0 bg-gradient-to-r from-crimson via-crimson/20 to-transparent" aria-hidden="true"></div>
            </div>
        </div>
    </section>

    {{-- Blood group chips --}}
    <section class="container-page mt-8" aria-labelledby="groups-heading">
        <h2 id="groups-heading" data-reveal class="text-2xl font-bold md:text-3xl">রক্তের গ্রুপ বেছে নিন</h2>
        <x-site.group-chips class="mt-3" />
    </section>

    {{-- Recent requests + the post-a-request action (replaces the mockup's WhatsApp strip: no verified number exists) --}}
    @php($empty = $requests->isEmpty())
    <section class="container-page mt-10" aria-labelledby="requests-heading">
        <div class="flex items-end justify-between gap-4">
            <h2 id="requests-heading" data-reveal class="text-2xl font-bold md:text-3xl">সাম্প্রতিক রক্তের চাহিদা</h2>
            @unless ($empty)
                <a href="{{ route('requests.index') }}" wire:navigate class="shrink-0 text-[0.9375rem] font-semibold text-crimson hover:underline">সব দেখুন</a>
            @endunless
        </div>

        <div @class(['mt-3 grid gap-3', 'lg:grid-cols-12' => $empty])>
            @if ($empty)
                <div data-reveal class="card flex gap-4 p-5 sm:p-6 lg:col-span-6 lg:items-center">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-leaf-soft text-leaf" aria-hidden="true">
                        <x-lucide-clipboard-check class="size-6" />
                    </span>
                    <div>
                        <h3 class="font-sans text-lg font-bold">বোর্ডে এখন কোনো খোলা অনুরোধ নেই</h3>
                        <p class="mt-1 text-[0.9375rem] text-ink-soft">নতুন অনুরোধ পোস্ট হলে সবার আগে এখানেই দেখাবে। রক্ত দিতে প্রস্তুত থাকলে প্রোফাইলে "এখন দিতে পারবেন" চালু রাখুন।</p>
                    </div>
                </div>
            @else
                <div data-reveal-stagger class="grid gap-3 lg:grid-cols-3">
                    @foreach ($requests as $request)
                        <x-site.request-card :request="$request" compact />
                    @endforeach
                </div>
            @endif

            <div data-reveal @class([
                'relative isolate overflow-hidden rounded-xl bg-crimson p-5 text-white shadow-panel sm:p-6',
                'lg:col-span-6' => $empty,
                'sm:flex sm:items-center sm:justify-between sm:gap-6' => ! $empty,
            ])>
                <x-lucide-droplet class="pointer-events-none absolute -right-6 -bottom-8 -z-10 size-40 rotate-12 fill-white/10 text-white/10" aria-hidden="true" />
                <div class="flex gap-4">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-white text-crimson" aria-hidden="true"><x-lucide-siren class="size-6" /></span>
                    <div>
                        <h3 class="font-display text-xl font-bold text-white">জরুরি রক্ত প্রয়োজন?</h3>
                        <p class="mt-1 max-w-md text-[0.9375rem] text-white/90">রক্তের গ্রুপ, হাসপাতাল আর সময় দিয়ে অনুরোধ পোস্ট করুন — ডোনাররা সরাসরি আপনাকে কল করবেন। অ্যাকাউন্ট লাগবে না।</p>
                    </div>
                </div>
                <div @class(['mt-5 flex flex-wrap gap-2.5', 'sm:pl-16' => $empty, 'sm:mt-0 sm:shrink-0' => ! $empty])>
                    <x-ui.button :href="route('requests.create')" variant="inverse"><x-lucide-send aria-hidden="true" />অনুরোধ পোস্ট করুন</x-ui.button>
                    <x-ui.button :href="route('donors.search')" class="border border-white/60 bg-transparent! hover:bg-white/10!"><x-lucide-search aria-hidden="true" />নিজেই ডোনার খুঁজুন</x-ui.button>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works: a real sequence, so numbered --}}
    <section class="container-page mt-12" aria-labelledby="how-heading">
        <h2 id="how-heading" data-reveal class="text-2xl font-bold md:text-3xl">কীভাবে কাজ করে</h2>
        <ol data-reveal-stagger class="mt-3 grid gap-3 md:grid-cols-3">
            @foreach ([
                ['ডোনার একবার নিবন্ধন করেন', 'নাম, মোবাইল, রক্তের গ্রুপ, এলাকা আর শেষ রক্তদানের তারিখ — দুই মিনিটের কাজ।'],
                ['গ্রুপ ও জেলা দিয়ে খোঁজা', 'রক্তদূত শুধু তাঁদেরই দেখায় যাঁরা প্রস্তুত এবং গত ৯০ দিনে রক্ত দেননি।'],
                ['সরাসরি কল বা এসএমএস', 'হাসপাতাল আর সময় ঠিক করুন। শর্ত অনুযায়ী ডোনারের যাতায়াত ও খাবারের খরচ রোগীর পরিবার দেবে।'],
            ] as $i => [$title, $text])
                <li class="card flex gap-4 p-5">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-crimson-soft font-display text-lg font-bold text-crimson" aria-hidden="true">{{ \App\Support\Bn::num($i + 1) }}</span>
                    <div>
                        <h3 class="font-sans text-lg font-bold">{{ $title }}</h3>
                        <p class="mt-1 text-ink-soft">{{ $text }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Conditions + what is RoktoDoot --}}
    <section class="container-page mt-12 grid gap-10 lg:grid-cols-12" aria-label="শর্তসমূহ ও রক্তদূত সম্পর্কে">
        <x-site.conditions class="lg:col-span-7" />

        <div data-reveal class="flex flex-col lg:col-span-5">
            <h2 class="text-2xl font-bold md:text-3xl">রক্তদূত কী?</h2>
            <p class="mt-1 text-ink-soft">স্বেচ্ছায় রক্তদাতাদের সংগঠন</p>
            <div class="card mt-4 flex flex-1 flex-col overflow-hidden">
                <div class="p-5 sm:p-6">
                    <p class="text-[1.0625rem] leading-relaxed">রক্তদূত একটি স্বয়ংক্রিয় রক্তসেবা — রক্তপ্রার্থীকে মুহূর্তেই স্বেচ্ছাসেবী রক্তদাতার সাথে যুক্ত করে। সবার জন্য সবসময় বিনামূল্যে।</p>
                    @php($whyIcons = ['bot', 'hand-coins', 'clock-4', 'shield-check'])
                    <ul class="mt-5 grid grid-cols-2 gap-2.5">
                        @foreach (config('bandhan.why') as $i => $point)
                            <li class="flex items-center gap-2.5 rounded-lg bg-blush px-2.5 py-3 text-[0.9375rem] font-semibold leading-snug sm:px-3 sm:text-base">
                                <x-dynamic-component :component="'lucide-'.($whyIcons[$i] ?? 'check')" class="size-5 shrink-0 text-crimson" aria-hidden="true" />
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="mt-auto flex items-center gap-3 border-t border-border bg-crimson-soft/60 px-5 py-4 sm:px-6">
                    <x-lucide-droplet class="size-5 shrink-0 fill-crimson text-crimson" aria-hidden="true" />
                    <p class="font-display text-[1.0625rem] leading-snug font-semibold text-crimson-deep">রক্ত দিন জীবন বাঁচান।<br>রক্ত নিন নিজে বাঁচুন।</p>
                </div>
                <a href="{{ route('about') }}" wire:navigate class="flex items-center justify-between gap-2 border-t border-border px-5 py-3.5 font-semibold text-crimson transition-colors hover:bg-blush sm:px-6">
                    রক্তদূত সম্পর্কে আরও জানুন
                    <x-lucide-chevron-right class="size-5" aria-hidden="true" />
                </a>
            </div>
        </div>
    </section>

    {{-- Join --}}
    <section class="container-page mt-12" aria-labelledby="join-heading">
        <div data-reveal class="flex flex-col gap-5 rounded-xl bg-ink p-6 text-white sm:p-8 md:flex-row md:items-center md:justify-between">
            <div class="max-w-xl">
                <h2 id="join-heading" class="text-2xl font-bold text-white md:text-3xl">একবার নিবন্ধন করুন, প্রয়োজনের সময় পাশে থাকুন</h2>
                <p class="mt-2 text-white/80">কখন রক্ত দিতে পারবেন তা আপনিই ঠিক করবেন — প্রোফাইল থেকে যেকোনো সময় বিরতি নিতে পারবেন।</p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-3">
                <x-ui.button :href="route('register')" size="lg"><x-lucide-user-round-plus aria-hidden="true" />ডোনার হিসেবে যুক্ত হন</x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.app>
