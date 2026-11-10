@php
    $columns = [
        'রক্ত খুঁজুন' => [
            ['ডোনার খুঁজুন', route('donors.search')],
            ['রক্তের অনুরোধ', route('requests.index')],
            ['অনুরোধ পোস্ট করুন', route('requests.create')],
        ],
        'রক্ত দিন' => [
            ['ডোনার হিসেবে যুক্ত হন', route('register')],
            ['ডোনার টিপস', route('tips')],
            ['লগইন', route('login')],
        ],
        'রক্তদূত' => [
            ['আমাদের সম্পর্কে', route('about')],
            ['ব্লগ', route('blog.index')],
            ['গ্যালারি', route('gallery')],
            ['ব্যবহারের শর্তাবলি', route('terms')],
            ['গোপনীয়তা নীতি', route('privacy')],
        ],
    ];
@endphp

<footer class="mt-10 bg-crimson-deep text-white">
    <div class="container-page grid gap-10 py-12 md:grid-cols-12 md:py-14">
        <div class="md:col-span-4">
            <a href="{{ route('home') }}" wire:navigate class="inline-block rounded-md" aria-label="রক্তদূত — হোম">
                <picture>
                    <source srcset="/images/brand/bandhan-logo-240.webp 1x, /images/brand/bandhan-logo-480.webp 2x" type="image/webp">
                    <img src="/images/brand/bandhan-logo-240.png" alt="রক্তদূত" width="240" height="119" loading="lazy" class="h-auto w-44">
                </picture>
            </a>
            <p class="mt-4 max-w-sm text-[0.9375rem] text-white/80">
                বাংলাদেশের একটি বিনামূল্যের স্বেচ্ছাসেবী রক্তদাতা নেটওয়ার্ক। এখানে কেউ টাকার বিনিময়ে রক্ত দেন না, আর ডোনার খুঁজতেও কোনো টাকা লাগে না।
            </p>
        </div>

        <nav aria-label="ফুটার মেনু" class="grid grid-cols-2 gap-8 sm:grid-cols-3 md:col-span-5">
            @foreach ($columns as $heading => $links)
                <div>
                    <h2 class="font-sans text-sm font-semibold text-white">{{ $heading }}</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($links as [$label, $href])
                            <li><a href="{{ $href }}" wire:navigate class="text-[0.9375rem] text-white/75 hover:text-white hover:underline">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="md:col-span-3">
            <h2 class="font-sans text-sm font-semibold text-white">যোগাযোগ</h2>
            <ul class="mt-3 space-y-3 text-[0.9375rem]">
                <li>
                    <a href="mailto:{{ \App\Support\Site::email() }}" class="inline-flex items-center gap-2 break-all text-white/85 hover:text-white hover:underline">
                        <x-lucide-mail class="size-4 shrink-0" aria-hidden="true" />{{ \App\Support\Site::email() }}
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-page flex flex-col items-center gap-2 py-5 text-center text-sm text-white/65 sm:flex-row sm:justify-between sm:gap-4 sm:text-left">
            <p>&copy; {{ \App\Support\Bn::num((string) now()->year) }} রক্তদূত — {{ config('bandhan.subtitle_bn') }}</p>
            <p lang="en">
                Designed and Developed By
                <a href="https://digitalwebars.com/" target="_blank" rel="noopener" class="font-medium whitespace-nowrap text-white/90 hover:text-white hover:underline">Digital Webars</a>
            </p>
        </div>
    </div>
</footer>
