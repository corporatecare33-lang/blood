@if ($post->hasCover())
    @push('head')
        <link rel="preload" as="image" type="image/webp" imagesrcset="{{ $post->coverSrcset() }}" imagesizes="(min-width: 1024px) 760px, 100vw">
    @endpush
@endif

<x-layouts.app>
    <div class="container-page pt-4 md:pt-8">
        <x-site.breadcrumbs data-intro class="mb-5" />

        @unless ($post->isLive())
            <x-ui.alert title="প্রিভিউ" class="mb-5">
                {{ $post->isScheduled() ? 'নির্ধারিত লেখা — '.\App\Support\Bn::dateTime($post->published_at).'-এ প্রকাশ হবে।' : 'খসড়া — এখনো প্রকাশ হয়নি।' }}
                শুধু অ্যাডমিনরা এই প্রিভিউ দেখতে পাচ্ছেন।
                <a href="{{ route('admin.posts.edit', $post) }}" wire:navigate class="font-semibold underline">সম্পাদনা করুন</a>
            </x-ui.alert>
        @endunless

        <div class="grid gap-8 lg:grid-cols-12 lg:items-start lg:gap-10">
            <article class="card overflow-hidden lg:col-span-8" aria-labelledby="post-title">
                <header data-intro class="px-5 pt-6 pb-5 sm:px-8 sm:pt-8 lg:px-10 lg:pt-10">
                    <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-soft">
                        @if ($post->published_at)
                            <time datetime="{{ $post->published_at->toAtomString() }}">{{ \App\Support\Bn::date($post->published_at) }}</time>
                            <span aria-hidden="true">·</span>
                        @endif
                        <span>{{ \App\Support\Bn::num($post->readingMinutes()) }} মিনিটে পড়া</span>
                    </p>
                    <h1 id="post-title" class="mt-2 text-[1.75rem] leading-[1.2] font-bold text-balance md:text-[2.25rem] lg:text-[2.5rem]">{{ $post->title }}</h1>
                    @if ($post->excerpt)
                        <p class="mt-3 text-[1.0625rem] text-ink-soft md:text-lg">{{ $post->excerpt }}</p>
                    @endif
                </header>

                @if ($post->hasCover())
                    <figure data-intro class="bg-crimson-soft">
                        <img src="{{ $post->coverUrl() }}" srcset="{{ $post->coverSrcset() }}" sizes="(min-width: 1024px) 760px, 100vw"
                             alt="{{ $post->coverAlt() }}" width="{{ $post->cover_width }}" height="{{ $post->cover_height }}"
                             fetchpriority="high" decoding="async" class="h-auto max-h-[36rem] w-full object-cover">
                    </figure>
                @endif

                <div class="prose-bandhan prose-post px-5 py-7 sm:px-8 sm:py-9 lg:px-10">
                    {!! $post->bodyHtml() !!}
                </div>

                <footer class="flex flex-wrap items-center gap-2.5 border-t border-border px-5 py-4 sm:px-8 lg:px-10"
                        x-data="{
                            copied: false,
                            canShare: false,
                            init() { this.canShare = typeof navigator.share === 'function' },
                            share() { navigator.share({ title: @js($post->title), url: @js($post->url()) }).catch(() => {}) },
                            copy() {
                                navigator.clipboard?.writeText(@js($post->url())).then(() => {
                                    this.copied = true
                                    setTimeout(() => this.copied = false, 2500)
                                }).catch(() => {})
                            },
                        }">
                    <p class="mr-auto font-semibold">লেখাটি কাজে লাগলে অন্যদেরও জানান</p>
                    <x-ui.button type="button" size="sm" x-show="canShare" x-cloak x-on:click="share()"><x-lucide-share-2 aria-hidden="true" />শেয়ার করুন</x-ui.button>
                    <x-ui.button type="button" size="sm" variant="outline" x-on:click="copy()">
                        <x-lucide-link x-show="! copied" aria-hidden="true" /><x-lucide-check x-show="copied" x-cloak class="text-leaf" aria-hidden="true" />
                        <span x-text="copied ? 'লিংক কপি হয়েছে' : 'লিংক কপি করুন'">লিংক কপি করুন</span>
                    </x-ui.button>
                </footer>
            </article>

            <aside class="grid gap-6 lg:sticky lg:top-24 lg:col-span-4" aria-label="আরও">
                <div data-reveal class="relative isolate overflow-hidden rounded-xl bg-crimson p-5 text-white shadow-panel sm:p-6">
                    <x-lucide-droplet class="pointer-events-none absolute -right-6 -bottom-8 -z-10 size-36 rotate-12 fill-white/10 text-white/10" aria-hidden="true" />
                    <h2 class="font-display text-xl font-bold text-white">একটি নিবন্ধনেই বাঁচতে পারে একটি জীবন</h2>
                    <p class="mt-1.5 text-[0.9375rem] text-white/90">ডোনার হিসেবে যুক্ত হন, অথবা পরিচিত কারো রক্ত দরকার হলে অনুরোধ পোস্ট করুন।</p>
                    <div class="mt-5 grid gap-2.5">
                        <x-ui.button :href="route('register')" variant="inverse"><x-lucide-user-plus aria-hidden="true" />ডোনার হিসেবে যুক্ত হন</x-ui.button>
                        <x-ui.button :href="route('requests.create')" class="border border-white/60 bg-transparent! hover:bg-white/10!"><x-lucide-send aria-hidden="true" />রক্তের অনুরোধ পোস্ট করুন</x-ui.button>
                    </div>
                </div>

                @if ($more->isNotEmpty())
                    <section data-reveal aria-labelledby="more-heading">
                        <div class="flex items-end justify-between gap-3">
                            <h2 id="more-heading" class="text-xl font-bold">আরও পড়ুন</h2>
                            <a href="{{ route('blog.index') }}" wire:navigate class="text-[0.9375rem] font-semibold text-crimson hover:underline">সব লেখা</a>
                        </div>
                        <div class="mt-3 grid gap-3">
                            @foreach ($more as $item)
                                <x-site.post-card :post="$item" layout="row" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</x-layouts.app>
