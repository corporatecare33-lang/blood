{{-- Mobile bottom tab bar (mockup): হোম · ডোনার · রিকোয়েস্ট · প্রোফাইল. No motion attributes here. --}}
@php
    $tabs = [
        ['হোম', route('home'), 'house', true],
        ['ডোনার', route('donors.search'), 'droplet', false],
        ['রিকোয়েস্ট', route('requests.index'), 'heart-handshake', false],
        ['প্রোফাইল', auth()->check() ? route('account') : route('login'), 'circle-user-round', false],
    ];
@endphp
<nav aria-label="ট্যাব মেনু" class="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-white shadow-bar lg:hidden" style="padding-bottom: env(safe-area-inset-bottom)">
    <ul class="mx-auto grid h-[4.25rem] max-w-md grid-cols-4">
        @foreach ($tabs as [$label, $href, $icon, $exact])
            <li>
                <a href="{{ $href }}" wire:navigate
                   @if ($exact) wire:current.exact="text-crimson! [&_svg]:fill-crimson/15" @else wire:current="text-crimson! [&_svg]:fill-crimson/15" @endif
                   class="flex h-full flex-col items-center justify-center gap-0.5 text-[0.8125rem] font-medium text-ink-soft transition-colors hover:text-crimson">
                    <x-dynamic-component :component="'lucide-'.$icon" class="size-6" aria-hidden="true" />
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
