<form wire:submit="login" novalidate class="grid gap-5">
    <x-ui.field label="মোবাইল নম্বর" for="admin-mobile" required error="mobile">
        <div class="relative">
            <x-lucide-smartphone class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-ink-soft" aria-hidden="true" />
            <x-ui.input id="admin-mobile" type="tel" inputmode="numeric" wire:model="mobile" error="mobile" autocomplete="username" placeholder="01XXXXXXXXX" maxlength="14" class="pl-9" lang="en" required autofocus />
        </div>
    </x-ui.field>

    <x-ui.field label="পাসওয়ার্ড" for="admin-password" required error="password">
        <div x-data="{ show: false }" class="relative">
            <x-lucide-lock-keyhole class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-ink-soft" aria-hidden="true" />
            <x-ui.input id="admin-password" x-bind:type="show ? 'text' : 'password'" type="password" wire:model="password" error="password" autocomplete="current-password" class="pr-12 pl-9" required />
            <button type="button" x-on:click="show = !show" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-ink-soft hover:text-ink" x-bind:aria-pressed="show">
                <span class="sr-only">পাসওয়ার্ড দেখুন</span>
                <x-lucide-eye class="size-5" x-show="!show" aria-hidden="true" />
                <x-lucide-eye-off class="size-5" x-show="show" x-cloak aria-hidden="true" />
            </button>
        </div>
    </x-ui.field>

    <div class="flex items-center justify-between gap-4">
        <label class="flex cursor-pointer items-center gap-2 text-sm">
            <input type="checkbox" wire:model="remember" class="ui-checkbox"> এই ডিভাইসে লগইন থাকুন
        </label>
        <a href="{{ route('password.request') }}" wire:navigate class="text-sm font-semibold text-crimson hover:underline">পাসওয়ার্ড ভুলে গেছেন?</a>
    </div>

    <x-ui.button class="w-full" size="lg" wire:loading.attr="disabled" wire:target="login">
        <x-ui.spinner wire:loading wire:target="login" />
        <span wire:loading.remove wire:target="login">লগইন করুন</span>
        <span wire:loading wire:target="login">যাচাই হচ্ছে…</span>
    </x-ui.button>
</form>
