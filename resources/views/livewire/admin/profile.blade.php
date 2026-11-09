@php($user = auth()->user())

<div class="grid max-w-3xl gap-6">
    <div class="admin-card flex items-center gap-4 p-5">
        <x-ui.avatar :user="$user" size="size-14 text-lg" />
        <div class="min-w-0">
            <p class="truncate text-lg font-semibold">{{ $user->name }}</p>
            <p class="text-sm text-muted-foreground"><span lang="en">{{ $user->mobile }}</span> · {{ $user->role->label() }}</p>
        </div>
    </div>

    <form wire:submit="saveDetails" novalidate>
        <x-ui.card title="নাম ও ইমেইল" description="লগইনের মোবাইল নম্বর এখান থেকে বদলানো যায় না।">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="নাম" for="p-name" required error="name">
                    <x-ui.input compact id="p-name" wire:model="name" error="name" autocomplete="name" />
                </x-ui.field>
                <x-ui.field label="ইমেইল" for="p-email" error="email">
                    <x-ui.input compact id="p-email" type="email" wire:model="email" error="email" autocomplete="email" lang="en" />
                </x-ui.field>
            </div>
            <x-slot:footer>
                <x-ui.button size="sm" class="ml-auto" wire:loading.attr="disabled" wire:target="saveDetails"><x-ui.spinner wire:loading wire:target="saveDetails" />সংরক্ষণ করুন</x-ui.button>
            </x-slot:footer>
        </x-ui.card>
    </form>

    <form wire:submit="savePassword" novalidate>
        <x-ui.card title="পাসওয়ার্ড বদলান" description="কমপক্ষে ১০ অক্ষর, অক্ষর ও সংখ্যা দুটোই থাকতে হবে। বদলালে অন্য সব ডিভাইস থেকে লগআউট হবে।">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="বর্তমান পাসওয়ার্ড" for="p-current" required error="current_password" class="sm:col-span-2 sm:max-w-[calc(50%-0.5rem)]">
                    <x-ui.input compact id="p-current" type="password" wire:model="current_password" error="current_password" autocomplete="current-password" />
                </x-ui.field>
                <x-ui.field label="নতুন পাসওয়ার্ড" for="p-new" required error="password">
                    <x-ui.input compact id="p-new" type="password" wire:model="password" error="password" autocomplete="new-password" />
                </x-ui.field>
                <x-ui.field label="নতুন পাসওয়ার্ড আবার" for="p-confirm" required error="password_confirmation">
                    <x-ui.input compact id="p-confirm" type="password" wire:model="password_confirmation" autocomplete="new-password" />
                </x-ui.field>
            </div>
            <x-slot:footer>
                <x-ui.button size="sm" class="ml-auto" wire:loading.attr="disabled" wire:target="savePassword"><x-ui.spinner wire:loading wire:target="savePassword" />পাসওয়ার্ড বদলান</x-ui.button>
            </x-slot:footer>
        </x-ui.card>
    </form>
</div>
