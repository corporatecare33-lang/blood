@php(\App\Support\Seo::make()->title($title)->appBar('ব্লাডবন্ড')->description($message)->noindex()->withoutCanonical())
<x-layouts.app>
    <section class="container-page max-w-3xl! py-10 md:py-20">
        <div class="card grid items-center gap-6 p-6 sm:p-10 md:grid-cols-[auto_1fr] md:gap-10">
            <p class="group-glyph text-[5.5rem] text-crimson md:text-[8rem]" aria-hidden="true">{{ \App\Support\Bn::num((string) $code) }}</p>
            <div>
                <h1 class="text-[1.75rem] font-bold md:text-4xl">{{ $title }}</h1>
                <p class="mt-3 text-ink-soft md:text-lg">{{ $message }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button :href="route('home')"><x-lucide-house aria-hidden="true" />হোমে ফিরে যান</x-ui.button>
                    <x-ui.button :href="route('requests.index')" variant="soft">রক্তের অনুরোধ দেখুন</x-ui.button>
                </div>
            </div>
        </div>
        @if ($code == 404)
            <nav aria-label="জনপ্রিয় পাতা" class="mt-6 px-1">
                <h2 class="font-sans text-[0.9375rem] font-semibold">আপনি হয়তো এগুলো খুঁজছেন</h2>
                <ul class="mt-2 flex flex-wrap gap-x-6 gap-y-2">
                    <li><a href="{{ route('donors.search') }}" wire:navigate class="text-crimson underline-offset-2 hover:underline">ডোনার খুঁজুন</a></li>
                    <li><a href="{{ route('register') }}" wire:navigate class="text-crimson underline-offset-2 hover:underline">ডোনার হিসেবে যুক্ত হন</a></li>
                    <li><a href="{{ route('requests.create') }}" wire:navigate class="text-crimson underline-offset-2 hover:underline">রক্তের অনুরোধ পোস্ট করুন</a></li>
                    <li><a href="{{ route('about') }}" wire:navigate class="text-crimson underline-offset-2 hover:underline">ব্লাডবন্ড সম্পর্কে</a></li>
                </ul>
            </nav>
        @endif
    </section>
</x-layouts.app>
