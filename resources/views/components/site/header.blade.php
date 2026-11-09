@php
    $seo = \App\Support\Seo::make();
    $isHome = request()->routeIs('home');
    $user = auth()->user();
    $links = [
        ['হোম', route('home'), 'house', true],
        ['ডোনার খুঁজুন', route('donors.search'), 'search', false],
        ['রক্তের অনুরোধ', route('requests.index'), 'clipboard-list', false],
        ['ডোনার টিপস', route('tips'), 'heart-pulse', false],
        ['গ্যালারি', route('gallery'), 'image', false],
        ['আমাদের সম্পর্কে', route('about'), 'info', false],
    ];
@endphp

<header
    x-data="{ open: false }"
    x-on:keydown.escape.window="open = false"
    x-on:livewire:navigating.window="open = false"
    class="sticky top-0 z-40 bg-crimson text-white shadow-[0_1px_0_rgb(0_0_0/0.12)]"
>
    {{-- Mobile app bar (mockup): menu/back · centred title · action --}}
    <div class="grid h-14 grid-cols-[3rem_1fr_3rem] items-center px-1 lg:hidden">
        @if ($isHome)
            <button type="button" x-on:click="open = true" class="flex size-11 items-center justify-center rounded-lg hover:bg-white/10" aria-controls="site-drawer" x-bind:aria-expanded="open">
                <x-lucide-menu class="size-6" aria-hidden="true" /><span class="sr-only">মেনু খুলুন</span>
            </button>
            <a href="{{ route('home') }}" wire:navigate class="justify-self-center rounded-full border border-white/70 px-5 py-1 font-display text-lg font-bold" aria-label="ব্লাডবন্ড — হোম">ব্লাডবন্ড</a>
            <a href="{{ route('donors.search') }}" wire:navigate class="flex size-11 items-center justify-center rounded-lg hover:bg-white/10">
                <x-lucide-search class="size-6" aria-hidden="true" /><span class="sr-only">ডোনার খুঁজুন</span>
            </a>
        @else
            <button type="button" x-on:click="history.length > 1 ? history.back() : Livewire.navigate('{{ route('home') }}')" class="flex size-11 items-center justify-center rounded-lg hover:bg-white/10">
                <x-lucide-chevron-left class="size-7" aria-hidden="true" /><span class="sr-only">ফিরে যান</span>
            </button>
            <p class="truncate text-center font-display text-lg font-bold">{{ $seo->appBar ?? 'ব্লাডবন্ড' }}</p>
            <button type="button" x-on:click="open = true" class="flex size-11 items-center justify-center rounded-lg hover:bg-white/10" aria-controls="site-drawer" x-bind:aria-expanded="open">
                <x-lucide-menu class="size-6" aria-hidden="true" /><span class="sr-only">মেনু খুলুন</span>
            </button>
        @endif
    </div>

    {{-- Desktop bar --}}
    <div class="container-page hidden h-[4.5rem] items-center gap-6 lg:flex">
        <a href="{{ route('home') }}" wire:navigate class="-ml-1 shrink-0 rounded-md p-1" aria-label="ব্লাডবন্ড — হোম">
            <picture>
                <source srcset="/images/brand/bandhan-logo-240.webp 1x, /images/brand/bandhan-logo-480.webp 2x" type="image/webp">
                <img src="/images/brand/bandhan-logo-240.png" alt="ব্লাডবন্ড" width="240" height="119" class="h-12 w-auto">
            </picture>
        </a>
        <nav aria-label="প্রধান মেনু" class="ml-auto">
            <ul class="flex items-center gap-1">
                @foreach (array_slice($links, 1, 5) as [$label, $href])
                    <li>
                        <a href="{{ $href }}" wire:navigate wire:current="!bg-white/15 !text-white"
                           class="rounded-lg px-3 py-2 text-[0.9375rem] font-medium text-white/85 transition-colors hover:bg-white/10 hover:text-white">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
        <div class="flex items-center gap-2">
            @auth
                @if ($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="rounded-lg px-3 py-2 text-[0.9375rem] font-medium text-white/85 hover:bg-white/10 hover:text-white">অ্যাডমিন</a>
                @endif
                <a href="{{ route('account') }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-[0.9375rem] font-semibold text-crimson hover:bg-crimson-soft">
                    <x-lucide-circle-user-round class="size-4" aria-hidden="true" />আমার প্রোফাইল
                </a>
            @else
                <a href="{{ route('login') }}" wire:navigate class="rounded-lg px-3 py-2 text-[0.9375rem] font-medium text-white/85 hover:bg-white/10 hover:text-white">লগইন</a>
                <a href="{{ route('register') }}" wire:navigate class="rounded-lg bg-white px-4 py-2 text-[0.9375rem] font-semibold text-crimson hover:bg-crimson-soft">ডোনার হোন</a>
            @endauth
        </div>
    </div>

    {{-- Drawer (mobile) --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="মেনু">
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-ink/50" x-on:click="open = false"></div>
            <nav id="site-drawer" x-show="open" x-trap.noscroll="open"
                 x-transition:enter="transition duration-250 ease-out" x-transition:enter-start="-translate-x-full"
                 x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="-translate-x-full"
                 class="absolute inset-y-0 left-0 flex w-[min(20rem,85vw)] flex-col overflow-y-auto bg-white shadow-xl">
                <div class="flex items-center justify-between bg-crimson px-4 py-4">
                    <picture>
                        <source srcset="/images/brand/bandhan-logo-240.webp 1x, /images/brand/bandhan-logo-480.webp 2x" type="image/webp">
                        <img src="/images/brand/bandhan-logo-240.png" alt="ব্লাডবন্ড" width="240" height="119" class="h-11 w-auto">
                    </picture>
                    <button type="button" x-on:click="open = false" class="flex size-10 items-center justify-center rounded-lg text-white hover:bg-white/10">
                        <x-lucide-x class="size-6" aria-hidden="true" /><span class="sr-only">মেনু বন্ধ করুন</span>
                    </button>
                </div>
                <ul class="grid gap-0.5 p-3">
                    @foreach ($links as [$label, $href, $icon, $exact])
                        <li>
                            <a href="{{ $href }}" wire:navigate @if ($exact) wire:current.exact="bg-crimson-soft text-crimson!" @else wire:current="bg-crimson-soft text-crimson!" @endif
                               class="flex items-center gap-3 rounded-lg px-3 py-3 text-base font-medium text-ink hover:bg-muted">
                                <x-dynamic-component :component="'lucide-'.$icon" class="size-5 text-crimson" aria-hidden="true" />{{ $label }}
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('requests.create') }}" wire:navigate class="flex items-center gap-3 rounded-lg px-3 py-3 text-base font-medium text-ink hover:bg-muted">
                            <x-lucide-plus class="size-5 text-crimson" aria-hidden="true" />রক্তের অনুরোধ পোস্ট করুন
                        </a>
                    </li>
                </ul>
                <div class="mt-auto grid gap-2 border-t border-border p-4">
                    @auth
                        @if ($user->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" wire:navigate class="rounded-lg border border-input px-4 py-2.5 text-center font-semibold">অ্যাডমিন প্যানেল</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full rounded-lg px-4 py-2.5 font-semibold text-crimson hover:bg-crimson-soft">লগআউট</button>
                        </form>
                    @else
                        <a href="{{ route('register') }}" wire:navigate class="rounded-lg bg-crimson px-4 py-2.5 text-center font-semibold text-white">ডোনার হিসেবে যুক্ত হন</a>
                        <a href="{{ route('login') }}" wire:navigate class="rounded-lg border border-input px-4 py-2.5 text-center font-semibold">লগইন</a>
                    @endauth
                </div>
            </nav>
        </div>
    </template>
</header>
