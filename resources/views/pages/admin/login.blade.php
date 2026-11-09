@php(\App\Support\Seo::make()->title('অ্যাডমিন লগইন')->description('ব্লাডবন্ড অ্যাডমিন প্যানেলে লগইন করুন।')->noindex())

<x-layouts.admin-base body-class="bg-sidebar">
    <main id="content" tabindex="-1" class="flex min-h-dvh items-center justify-center px-4 py-10">
        <div class="w-full max-w-sm md:max-w-4xl">
            <div data-intro class="grid overflow-hidden rounded-2xl border border-sidebar-border bg-white shadow-pop md:grid-cols-2">
                <div class="p-6 sm:p-10">
                    <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2.5 rounded-lg md:hidden">
                        <span class="flex size-9 items-center justify-center rounded-lg bg-crimson text-white" aria-hidden="true"><x-lucide-droplet class="size-4 fill-white" /></span>
                        <span class="font-display text-xl font-bold">ব্লাডবন্ড</span>
                    </a>

                    <div class="mt-8 md:mt-0">
                        <p class="inline-flex items-center gap-1.5 rounded-full bg-crimson-soft px-2.5 py-0.5 text-xs font-semibold text-crimson-deep">
                            <x-lucide-shield-check class="size-3.5" aria-hidden="true" />শুধু অ্যাডমিনদের জন্য
                        </p>
                        <h1 class="mt-3 font-display text-[1.75rem] font-bold">অ্যাডমিন প্যানেলে লগইন</h1>
                        <p class="mt-1 text-sm text-muted-foreground">অ্যাডমিন অ্যাকাউন্টের মোবাইল নম্বর আর পাসওয়ার্ড দিন।</p>
                    </div>

                    <div class="mt-7">
                        <livewire:admin.login />
                    </div>

                    <p class="mt-8 text-center text-sm text-muted-foreground">
                        ডোনার? <a href="{{ route('login') }}" wire:navigate class="font-semibold text-crimson underline-offset-4 hover:underline">সাধারণ লগইন পাতায় যান</a>
                    </p>
                </div>

                <div class="relative hidden flex-col justify-between overflow-hidden bg-crimson p-10 text-white md:flex">
                    <div aria-hidden="true" class="pointer-events-none absolute -right-24 -bottom-24 size-80 rounded-full bg-white/8"></div>
                    <div aria-hidden="true" class="pointer-events-none absolute -top-20 -right-16 size-56 rounded-full bg-crimson-deep/50"></div>

                    <a href="{{ route('home') }}" wire:navigate class="relative self-start rounded-md">
                        <picture>
                            <source srcset="/images/brand/bandhan-logo-240.webp 1x, /images/brand/bandhan-logo-480.webp 2x" type="image/webp">
                            <img src="/images/brand/bandhan-logo-240.png" alt="ব্লাডবন্ড — হোম" width="240" height="119" class="h-14 w-auto">
                        </picture>
                    </a>

                    <div class="relative">
                        <p class="font-display text-[1.75rem] leading-snug font-bold">{{ config('bandhan.tagline_bn') }}</p>
                        <p class="mt-3 text-white/80">{{ config('bandhan.subtitle_bn') }}</p>
                        <ul class="mt-8 grid gap-2.5 text-sm text-white/90">
                            <li class="flex items-center gap-2.5"><x-lucide-users class="size-4 shrink-0" aria-hidden="true" />ডোনার ও রক্তের অনুরোধ পরিচালনা</li>
                            <li class="flex items-center gap-2.5"><x-lucide-images class="size-4 shrink-0" aria-hidden="true" />গ্যালারি ও পাতার লেখা হালনাগাদ</li>
                            <li class="flex items-center gap-2.5"><x-lucide-settings class="size-4 shrink-0" aria-hidden="true" />যোগাযোগের তথ্য ও শর্তসমূহ সম্পাদনা</li>
                        </ul>
                    </div>
                </div>
            </div>

            <p class="mt-6 text-center text-sm text-muted-foreground">
                <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-1.5 hover:text-ink"><x-lucide-arrow-left class="size-4" aria-hidden="true" />সাইটে ফিরে যান</a>
            </p>
        </div>
    </main>
</x-layouts.admin-base>
